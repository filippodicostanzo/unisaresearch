<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;
use Illuminate\Support\Collection;

class PaperSubmission extends Mailable
{
    use Queueable, SerializesModels;

    protected $author;
    protected $post;
    protected $coauthors;

    /**
     * Creiamo una nuova istanza della mail
     * @param mixed $author L'autore principale del paper
     * @param mixed $post Il paper sottomesso
     * @param Collection|array $coauthors I co-autori del paper
     */
    public function __construct($author, $post, $coauthors)
    {
        $this->author = $author;
        $this->post = $post;
        // Convertiamo $coauthors in Collection se non lo è già
        $this->coauthors = $coauthors instanceof Collection ? $coauthors : collect($coauthors);
    }

    /**
     * Costruisce l'email usando il template dal database
     * @return $this
     */
    public function build()
    {
        // Recuperiamo il template dal database o ne creiamo uno di default
        $template = EmailTemplate::where('template', 'paper-submission')->first();

        if (!$template) {
            $template = new EmailTemplate([
                'name' => 'paper-submission',
                'subject' => 'Naples Forum: Paper Submission Received',
                'template' => 'submission',
                'body' => $this->getDefaultTemplate(),
                'active' => true
            ]);

            $template->save();
        }

        // Sostituiamo le variabili nel template
        $body = $this->replaceVariables($template->body);

        return $this->subject($template->subject)
            ->view('mail.papersubmission')
            ->with([
                'emailContent' => $body
            ]);
    }

    /**
     * Sostituisce le variabili nel template con i valori effettivi
     * @param string $content Il contenuto del template
     * @return string
     */
    protected function replaceVariables($content)
    {
        // Aggiungiamo logging per debug
        \Log::info('Debug variables:', [
            'author' => $this->author,
            'post' => $this->post,
            'coauthors' => $this->coauthors
        ]);

        $templateName = $this->post->template_fk ?
            $this->post->template_fk->name :
            'Paper';  // Valore di fallback se la relazione è vuota

        $variables = [
            '{$name}' => '<strong>' . ($this->author->name ?? '') . '</strong>',
            '{$surname}' => '<strong>' . ($this->author->surname ?? '') . '</strong>',
            '{$email}' => '<strong>' . ($this->author->email ?? '') . '</strong>',
            '{$title}' => '<strong>' . ($this->post->title ?? '') . '</strong>',
            '{$template}' => '<strong>' . $templateName . '</strong>',
            '{$coauthors}' => $this->formatCoauthorsList()
        ];


        return strtr($content, $variables);
    }

    /**
     * Formatta la lista dei co-autori in una stringa leggibile
     * @return string
     */
    protected function formatCoauthorsList()
    {
        if ($this->coauthors->isEmpty()) {
            return 'no co-authors';
        }

        $formattedNames = $this->coauthors
            ->map(function($coauthor) {
                // Usiamo firstname e lastname invece di name e surname
                $firstName = $coauthor->firstname ?? ($coauthor['firstname'] ?? '');
                $lastName = $coauthor->lastname ?? ($coauthor['lastname'] ?? '');

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

    /**
     * Restituisce il template di default per l'email
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '
            <h1>Paper Submitted</h1>
            <p>Dear {$name} {$surname},</p>
            <p>We received your submission for the {$template} titled {$title} co-authored with {$coauthors}.</p>
            <p>If this attribution has been done by mistake, do not reply to this email.</p>
            <p>Please, contact naplesforumonservice@gmail.com.</p>
            <p>With our Best regards,</p>
            <p>The Naples Forum Secretariat</p>
        ';
    }
}
