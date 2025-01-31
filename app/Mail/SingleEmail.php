<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SingleEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $author;
    protected $post;
    protected $coauthors;
    protected $emailSubject;
    protected $emailBody;
    protected $recipientType;
    protected $isReviewer;
    protected $reviewer;

    public function __construct($author, $post, $coauthors, $subject, $body, $recipientType)
    {
        $this->author = $author;
        $this->post = $post instanceof Post ? $post : Post::find($post['id']);
        $this->coauthors = $coauthors instanceof Collection ? $coauthors : collect($coauthors);
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->recipientType = $recipientType;
        $this->isReviewer = $recipientType === 'reviewer';
        $this->reviewer = $this->isReviewer ? $author : null; // Per i reviewer, $author contiene i dati del reviewer
    }

    public function build()
    {
        $body = $this->replaceVariables($this->emailBody);

        return $this->subject($this->emailSubject)
            ->view('mail.singleemail')
            ->with([
                'emailContent' => $body,
                'isReviewer' => $this->isReviewer
            ]);
    }

    protected function replaceVariables($content)
    {
        if ($this->isReviewer) {
            return $this->replaceReviewerVariables($content);
        }
        return $this->replaceAuthorVariables($content);
    }

    protected function replaceReviewerVariables($content)
    {
        $variables = [
            '{$reviewer_name}' => '<strong>' . ($this->reviewer['name'] ?? '') . '</strong>',
            '{$reviewer_surname}' => '<strong>' . ($this->reviewer['surname'] ?? '') . '</strong>',
            '{$title}' => '<strong>' . ($this->post['title'] ?? '') . '</strong>',
            '{$template}' => '<strong>' . ($this->post['template_fk']['name'] ?? '') . '</strong>',
            '{$comments}' => $this->getCommentText()
        ];

        return strtr($content, $variables);
    }

    protected function replaceAuthorVariables($content)
    {
        $variables = [
            '{$name}' => '<strong>' . ($this->author['name'] ?? '') . '</strong>',
            '{$surname}' => '<strong>' . ($this->author['surname'] ?? '') . '</strong>',
            '{$email}' => '<strong>' . ($this->author['email'] ?? '') . '</strong>',
            '{$title}' => '<strong>' . ($this->post['title'] ?? '') . '</strong>',
            '{$coauthors}' => $this->formatCoauthorsList(),
            '{$template}' => '<strong>' . ($this->post['template_fk']['name'] ?? '') . '</strong>',
            '{$comments}' => $this->getCommentText()
        ];

        return strtr($content, $variables);
    }

    protected function getCommentText()
    {
        $comment = $this->post->comments()->first();
        return $comment && !empty($comment->comment) ? '<strong>' . $comment->comment . '</strong>' : '';
    }

    protected function formatCoauthorsList()
    {
        if ($this->coauthors->isEmpty()) {
            return 'no co-authors';
        }

        $formattedNames = $this->coauthors
            ->map(function($coauthor) {
                $name = $coauthor['name'] ?? '';
                $surname = $coauthor['surname'] ?? '';

                if (empty($name) && empty($surname)) {
                    return '';
                }

                return '<strong>' . trim("$name $surname") . '</strong>';
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
