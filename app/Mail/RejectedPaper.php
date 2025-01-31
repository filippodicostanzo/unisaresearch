<?php

namespace App\Mail;

use App\Models\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class RejectedPaper extends Mailable
{
    use Queueable, SerializesModels;

    // Manteniamo una singola proprietà per i dettagli del post
    protected $details;

    /**
     * Create a new message instance.
     * @param mixed $details Il post rifiutato con le relazioni
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message using the template from the database.
     * @return $this
     */
    public function build()
    {
        // Recuperiamo il template dal database
        $template = EmailTemplate::where('template', 'rejected-paper')->first();

        if (!$template) {
            // Se il template non esiste, creiamo un template di default
            $template = new EmailTemplate([
                'name' => 'rejected-paper',
                'subject' => 'Paper Status Update',
                'body' => $this->getDefaultTemplate()
            ]);

            $template->save();
        }

        // Sostituiamo le variabili nel template
        $body = $this->replaceVariables($template->body);

        return $this->subject($template->subject)
            ->view('mail.rejectedpaper')
            ->with([
                'emailContent' => $body,
                'post' => $this->details
            ]);
    }

    /**
     * Replace template variables with actual values.
     * @param string $content Il contenuto del template
     * @return string
     */
    protected function replaceVariables($content)
    {
        $variables = [
            '{$name}' => '<strong>' . $this->details->user_fk->name . '</strong>',
            '{$surname}' => '<strong>' . $this->details->user_fk->surname . '</strong>',
            '{$email}' => '<strong>' . $this->details->user_fk->email . '</strong>',
            '{$title}' => '<strong>' . $this->details->title . '</strong>',
            '{$coauthors}' => $this->formatCoauthorsList(),
            '{$template}' => '<strong>' . $this->details->template_fk->name . '</strong>',
        ];

        return strtr($content, $variables);
    }

    /**
     * Get the default template for the rejection email.
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '
            <h1>Paper Status Update</h1>
            <p>Dear {$name} {$surname},</p>
            <p>We regret to inform you that after careful consideration, your paper titled {$title} has not been accepted for the Naples Forum on Service.</p>
            <p>Due to the high number of submissions and our limited capacity, we had to make difficult decisions in our selection process.</p>
            <p>We appreciate your interest in the Naples Forum on Service and hope you will consider submitting to future editions.</p>
            <p>If you have any questions, please feel free to contact us.</p>
            <p>With our Best regards,<br>The Naples Forum Secretariat</p>
        ';
    }

    /**
     * Get the formatted list of coauthors.
     *
     * @return string
     */
    protected function formatCoauthorsList()
    {
        // Convert the authors string to a collection
        $authors = collect(explode(',', $this->details->authors));

        if ($authors->isEmpty()) {
            return 'no co-authors';
        }

        $formattedNames = $authors
            ->map(function($authorId) {
                $author = Author::find($authorId);
                if (!$author) {
                    return '';
                }

                $firstName = $author->firstname ?? '';
                $lastName = $author->lastname ?? '';

                if (empty($firstName) && empty($lastName)) {
                    return '';
                }

                return '<strong>' . trim("$firstName $lastName") . '</strong>';
            })
            ->filter()
            ->values();

        $count = $formattedNames->count();

        if ($count === 0) {
            return 'no co-authors';
        }

        if ($count === 1) {
            return $formattedNames->first();
        }

        if ($count === 2) {
            return $formattedNames->first() . ' and ' . $formattedNames->last();
        }

        $last = $formattedNames->pop();
        return $formattedNames->implode(', ') . ', and ' . $last;
    }
}
