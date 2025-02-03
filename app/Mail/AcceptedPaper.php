<?php

namespace App\Mail;

use App\Models\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class AcceptedPaper extends Mailable
{
    use Queueable, SerializesModels;

    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        $template = EmailTemplate::where('template', 'accepted-paper')->first();

        if (!$template) {
            // Se il template non esiste, creiamo un template di default
            $template = new EmailTemplate([
                'name' => 'accepted-paper',
                'subject' => 'Abstract Accepted',
                'body' => $this->getDefaultTemplate()
            ]);

            $template->save();
        }

        $body = $this->replaceVariables($template->body);

        return $this->subject($template->subject)
            ->view('mail.acceptedpaper')
            ->with([
                'emailContent' => $body,
                'post' => $this->details
            ]);
    }

    protected function replaceVariables($content)
    {
        $comment = $this->details->comments()->first();
        $commentText = $comment && $comment->comment ? 'Based on the comments received, we want to inform you that: ' . $comment->comment  : '';

        // Ora includiamo i tag strong direttamente nelle sostituzioni
        $variables = [
            '{$name}' => '<strong>' . $this->details->user_fk->name . '</strong>',
            '{$surname}' => '<strong>' . $this->details->user_fk->surname . '</strong>',
            '{$email}' => '<strong>' . $this->details->user_fk->email . '</strong>',
            '{$title}' => '<strong>' . $this->details->title . '</strong>',
            '{$coauthors}' => $this->formatCoauthorsList(),
            '{$template}' => '<strong>' . $this->details->template_fk->name . '</strong>',
            '{$comments}' => $commentText
        ];

        return strtr($content, $variables);
    }

    protected function getDefaultTemplate()
    {
        // Nel template di default, rimuoviamo i tag strong dalle variabili
        // perché verranno aggiunti dalla funzione replaceVariables
        return '
            <h1>Congratulations!</h1>
            <p>Dear {$name} {$surname},</p>
            <p>We are pleased to inform you that your abstract titled {$title} has been accepted by the Naples Forum on Service.</p>
            <p>We will contact you shortly with further details about your Forum participation.</p>
            <p>Feel free to contact us at naplesforumonservice@gmail.com for any enquiries.</p>
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
