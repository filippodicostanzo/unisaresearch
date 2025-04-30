<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .post {
            margin-bottom: 40px;
            page-break-after: always;
        }
        .post:last-child {
            page-break-after: avoid;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        .meta {
            font-size: 12px;
            margin-bottom: 15px;
            color: #555;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .content {
            font-size: 12px;
        }
        .content h2 {
            font-size: 16px;
            margin: 15px 0 10px;
            color: #444;
        }
        .separator {
            border-top: 2px solid #eee;
            margin: 30px 0;
        }
        .footer {
            font-size: 10px;
            text-align: center;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<h1 style="text-align: center; font-size: 20px; margin-bottom: 30px;">Naples Forum on Service - Selected Papers</h1>

@foreach($posts as $post)
    <div class="post">
        <h1>{{ $post->title }}</h1>

        <div class="meta">
            <p><strong>Author:</strong> {{ $post->user_fk->name }} {{ $post->user_fk->surname }}</p>

            @if(is_countable($post->authors) && count($post->authors) > 0)
                <p><strong>Co-Authors:</strong>
                    @foreach($post->authors as $index => $author)
                        {{ $author->firstname }} {{ $author->lastname }}@if($index < count($post->authors) - 1), @endif
                    @endforeach
                </p>
            @endif

            <p><strong>Topic:</strong> {{ $post->category_fk->name }}</p>
            <p><strong>Template:</strong> {{ $post->template_fk->name }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}</p>
            <p><strong>Status:</strong> {{ $post->state_fk->name }}</p>
        </div>

        <div class="content">
            @php
                $fields = json_decode($post->template_fk->fields);
            @endphp

            @if(is_array($fields) || is_object($fields))
                @for($i = 1; $i <= count($fields); $i++)
                    @php
                        $fieldName = 'field_' . $i;
                        $fieldContent = $post->$fieldName;
                    @endphp

                    @if(!empty($fieldContent))
                        <h2>{{ $fields[$i-1]->name }}</h2>
                        {!! $fieldContent !!}
                    @endif
                @endfor
            @endif

            @if(!empty($post->tags))
                <p><strong>Keywords:</strong> {{ $post->tags }}</p>
            @endif
        </div>

        @if(!$loop->last)
            <div class="separator"></div>
        @endif
    </div>
@endforeach

<div class="footer">
    <p>Generated on {{ date('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
