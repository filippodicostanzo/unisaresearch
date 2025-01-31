<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class ReviewerAssignment extends Mailable
{
    use Queueable, SerializesModels;

    protected $reviewer;
    protected $post;

    public function __construct($reviewer, $post)
    {
        $this->reviewer = $reviewer;
        $this->post = $post;
    }

    public function build()
    {
        $template = EmailTemplate::where('template', 'reviewer-assignment')->first();

        if (!$template) {
            $template = new EmailTemplate([
                'name' => 'Reviewer Assignment',
                'subject' => 'Naples Forum: Review Assignment',
                'body' => $this->getDefaultTemplate(),
                'template' => 'reviewer-assignment'
            ]);

            $template->save();
        }

        $body = $this->replaceVariables($template->body);

        return $this->subject($template->subject)
            ->view('mail.reviewerassignment')
            ->with([
                'emailContent' => $body,
                'reviewer' => $this->reviewer,
                'post' => $this->post
            ]);
    }

    protected function replaceVariables($content)
    {
        $variables = [
            '{$name}' => '<strong>' . $this->reviewer->name . '</strong>',
            '{$surname}' => '<strong>' . $this->reviewer->surname . '</strong>',
            '{$title}' => '<strong>' . $this->post->title . '</strong>',
            '{$email}' => '<strong>' . $this->reviewer->email . '</strong>',
            '{$coauthors}' => $this->getCoauthorsList(),
            '{$template}' => '<strong>' . $this->post->template_fk->name . '</strong>'
        ];

        return strtr($content, $variables);
    }

    protected function getDefaultTemplate()
    {
        return '
            <p>Dear {$name} {$surname}</p>
            <p>The Naples Forum on Service chairs ask you to review the abstract titled {$title}</p>
            <p>You can find the document in your Reviewer Section of the manuscript management platform available at https://www.nfos.it.</p>
            <p>If it is your first time there, please, register yourself using as login your email address {$email}</p>
            <p>Please, perform the review within 20 days.</p>
            <p>In case you want to decline this invitation, send an email to naplesforumonservice@gmail.com.</p>
            <p>Thank you for your support.</p>
            <p>With our Best regards,</p>
            <p>The Naples Forum Secretariat</p>
        ';
    }

    /**
     * Get the formatted list of coauthors.
     *
     * @return string
     */
    protected function getCoauthorsList()
    {
        $coauthors = array_map(function($author) {
            return $author['firstname'] . ' ' . $author['lastname'];
        }, $this->post->authors);

        return implode(', ', $coauthors);
    }

}
