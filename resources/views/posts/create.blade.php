@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content_header')
@stop

@php
    use App\Models\Author;
    use App\Models\Category;
    use App\Models\Template;
    use Illuminate\Support\Facades\Auth;

    $authors= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->get();
    $usr = Auth::user();
    $user = Auth::id();
    $categories = Category::where('visible',1)->orderBy('name','ASC')->get();
    $templates = Template::where('active',1)->orderBy('id')->get();
    $template = Template::where('active', 1)->first();
    $fields=json_decode($template->fields);

@endphp

@section('content')
    <div class="container post-form">
        <div class="row">
            <div class="col-lg-12 margin-tb">
                <div class="card card-mini">
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Create New Submission
                        </h1>
                        <div class="card-action">
                            <a href="{{ route('posts.author') }}">
                                <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="count_fields" value="{{count($fields)}}">
                        {!! Form::open(array('route' => 'posts.store','method'=>'POST', 'enctype' => 'multipart/form-data', 'id'=>'regForm')) !!}
                        @csrf

                        <!-- One "tab" for each step in the form: -->
                        <div class="tab">

                            <div class="form-group">
                                <div class="col-12"><label>Template</label></div>
                                <select id="template-selected" name="template" class="form-control">
                                    <option value="" data-type="">Choose</option>
                                    @foreach($templates as $item)
                                        <option value="{{$item->id}}" data-type="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Title:</label>
                                {!! Form::text('title', null, array('placeholder' => 'Title','class' => 'form-control',  'oninput'=>"this.className = ''", 'id'=>'title')) !!}
                            </div>

                            <div class="form-group row">
                                <div class="col-8"><label>List of Authors</label></div>
                                <div class="col-4 text-right">

                                    <button type="button" class="btn btn-sm btn-primary" id="addNewAuthorBtn">
                                        <i class="fas fa-plus"></i> Add New Co-Author
                                    </button>
                                </div>

                                @if(count($authors)==0)
                                    <div class="col-12 mb-3">Please, add your co-authors by clicking <a
                                            href="../authors">here</a> or on " My co-authors" on the left before adding
                                        a manuscript.
                                    </div>
                                @endif


                                <div class="item col-md-6 col-xs-6 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="created-checkbox" checked disabled
                                               tag="{{$usr->name}} {{$usr->surname}}">
                                        <label class="form-check-label"
                                               for="exampleCheck1">{{$usr->name}} {{$usr->surname}}</label>
                                    </div>
                                </div>
                                @foreach ($authors as $item)
                                    <div class="item col-md-6 col-xs-6 mb-3">
                                        <div class="form-check authors-checkbox">
                                            <input type="checkbox" id="{{$item->id}}"
                                                   name="authors[]" value="{{$item->id}}"
                                                   tag="{{$item->firstname}} {{$item->lastname}}"
                                                   data-author-id="{{$item->id}}"
                                                   data-author-name="{{$item->firstname}} {{$item->lastname}}"
                                            >
                                            <label class="form-check-label"
                                                   for="exampleCheck1">{{$item->firstname}} {{$item->lastname}}</label>
                                        </div>
                                    </div>
                                @endforeach


                                <div class="col-12">
                                    <div id="author_error"></div>
                                </div>

                                <!--
                                                                <div class="col-12">
                                                                    <p class="small">If the author is already present and has not been entered by you
                                                                        try to search via email  <a class="btn btn-primary" data-toggle="collapse" href="#collapseExample"
                                                                                                    role="button" aria-expanded="false" aria-controls="collapseExample">
                                                                            +
                                                                        </a></p>
                                                                </div>

                                                                <div class="col-12">

                                                                    <div class="collapse" id="collapseExample">
                                                                        <div class="card card-body">
                                                                            <input type="text" class="form-controller" id="search"
                                                                                   placeholder="Search By Email and press Enter" name="search">

                                                                        </div>

                                                                        <div class="authors-checkbox row"></div>


                                                                    </div>
                                                                </div>

                                -->

                            </div>

                            {{--                           <div class="form-group row">
                                                           <div class="col-12"><label>Authors Selected</label></div>
                                                           <div class="col-12">
                                                               <div class="co-authors"> {{$usr->name}} {{$usr->surname}}  </div>
                                                           </div>
                                                       </div>--}}

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Selected Authors Order</div>
                                    <div class="card-body">
                                        <ul id="selectedAuthors" class="list-group">
                                            <li class="list-group-item" data-author-id="0">
                                                {{$usr->name}} {{$usr->surname}}
                                                <span class="badge badge-primary ml-2">Submitter</span>
                                                <i class="fas fa-grip-lines float-right"></i>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-12"><label>Topic</label></div>
                                <select id="items-selected" name="category" class="form-control">
                                    @foreach($categories as $item)
                                        <option value="{{$item->id}}" data-type="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="modal fade" id="addAuthorModal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addAuthorModalLabel">Add New Co-Author</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Rimosso il tag form e sostituito con un div -->
                                            <div id="authorFormFields">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <input type="text" class="form-control" id="firstname"
                                                           name="firstname">
                                                </div>
                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="text" class="form-control" id="lastname"
                                                           name="lastname">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email">
                                                </div>
                                                <div class="alert alert-danger" id="authorFormError"
                                                     style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                            </button>
                                            <button type="button" class="btn btn-primary" id="saveAuthorBtn">Save
                                                Co-Author
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!--
                                                <div class="tab">
                                                    <div class="form-group">
                                                        <label>Abstract</label>
                                                        <textarea name="abstract" id="abstract" rows="10" cols="80"
                                                                  class="form-control"></textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Intro</label>
                                                        <textarea name="intro" id="intro" rows="10" cols="80"
                                                                  class="form-control"></textarea>
                                                    </div>
                                                </div>
                        -->
                        <div class="tab" id="textarea-section">
                            <!--  @foreach($fields as $key => $value)
                                <div class="form-group">
                                    <label>{{$value->name}}</label>

                                    <textarea name="field_{{$key+1}}" id="field_{{$key+1}}" rows="10" cols="80"
                                              class="form-control"></textarea>
                                </div>












                            @endforeach -->

                        </div>


                        <div class="tab">
                            <div class="form-group">
                                <label>
                                    Keywords:
                                </label>
                                {!! Form::text('tags', null, array('placeholder' => 'Keywords separated by comma','class' => 'form-control',  'oninput'=>"this.className = ''")) !!}
                            </div>
                            <div class="form-group imageUpload">
                                <label for="image">Upload Anonymus PDF</label>
                                <div class="note">
                                    <p class="small text-bold">When you upload the document, please be sure the names of
                                        the
                                        authors ARE NOT indicated.</p>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="pdf" data-input="document" data-preview="cover_preview"
                                           class="btn btn-secondary">
                                            <i class="fa fa-picture-o"></i> Choose
                                        </a>
                                        </span>
                                    {!! Form::text('pdf', null, array('placeholder' => 'Upload and select the file from File Manager','class' => 'form-control file-src','id' => 'document')) !!}

                                </div>

                            </div>
                            <!--<button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i> Save
                            </button> -->

                            <div class="form-group" id="error">

                            </div>
                        </div>

                        <div style="display: none" id="loader"><h3>Loading...</h3></div>
                        <div style="overflow:auto;">
                            <div style="float:left;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-primary">
                                    Previous
                                </button>
                            </div>

                            <div style="float:right" id="divSubmit"></div>
                            <div style="float:right; margin-right: 10px">

                                <button type="button" id="nextBtn" onclick="nextPrev(1)" class="btn btn-primary">Next
                                </button>

                            </div>


                        </div>

                        <!-- Circles which indicates the steps of the form: -->
                        <div style="text-align:center;margin-top:40px;">
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                        </div>


                        <div class="modal fade" id="modalSave" tabindex="-1" role="dialog"
                             aria-labelledby="modalSaveLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalSaveLabel">Notice</h5>
                                        <!--                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>-->
                                    </div>
                                    <div class="modal-body">
                                        Your submission has been saved as a draft. To submit, edit your manuscript and
                                        then push on "Submit for Review".
                                    </div>
                                    <div class="modal-footer">

                                        <!--                                        <button type="button" class="btn btn-primary" id="confirmSave">Save</button>-->
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="modal fade" id="modalSubmit" tabindex="-1" role="dialog"
                             aria-labelledby="modalSubmitLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalSubmitLabel">Submit for Review</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure to <b>submit the paper for review?</b><br> By doing so, you will no
                                        longer be able to modify it.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                        </button>
                                        <button type="button" class="btn btn-primary" id="confirmSubmit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{ Form::hidden('template', null, array('id'=>'template')) }}
                        {{ Form::hidden('created', $user) }}
                        {{ Form::hidden('coauthors', null, array('id'=>'coauthors')) }}
                        {{ Form::hidden('edit', $user) }}
                        {{ Form::hidden('source', null, array('id'=>'source')) }}
                        {{ Form::hidden('state', 1, array('id'=>'post_state')) }}
                        {{ Form::hidden('submitter_position', 1, array('id'=>'submitter_position')) }}


                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script type="text/javascript"
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
            integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
            crossorigin="anonymous"></script>

    <script src="../../ckeditor/ckeditor.js"></script>

    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

    <script>

        // Configurazione globale per CKEditor
        const options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}'
        };

        // Setup di base per AJAX
        $.ajaxSetup({headers: {'csrftoken': '{{ csrf_token() }}'}});

        const ManuscriptEditor = {
            // Variabili di stato
            currentTab: 0,
            authors: [],

            // Inizializzazione principale
            init() {
                this.initializeUI();
                this.initializeAuthors();
                this.setupEventListeners();
                this.showTab(this.currentTab);
            },

            // Inizializzazione dell'interfaccia utente
            initializeUI() {
                this.initializeCKEditor();
                this.initializeFileManager();
                this.initializeSortableList();
            },

            // Inizializzazione di CKEditor
            initializeCKEditor() {
                const textareas = document.getElementsByTagName("textarea");
                Array.from(textareas).forEach(textarea => {
                    CKEDITOR.replace(textarea.getAttribute('id'), options);
                });
            },

            // Inizializzazione del file manager
            initializeFileManager() {
                $('#pdf').filemanager('file', '', false);
            },

            // Inizializzazione della lista ordinabile
            initializeSortableList() {
                const selectedAuthorsList = document.getElementById('selectedAuthors');
                if (selectedAuthorsList) {
                    new Sortable(selectedAuthorsList, {
                        animation: 150,
                        handle: '.fa-grip-lines',
                        onEnd: (evt) => {
                            console.log('Drag and drop completato:');
                            console.log('Elemento spostato da posizione:', evt.oldIndex + 1);
                            console.log('Elemento spostato a posizione:', evt.newIndex + 1);

                            this.updateAuthorsOrder();

                            const currentOrder = $('#selectedAuthors li').map(function (index) {
                                return {
                                    position: index + 1,
                                    id: $(this).data('author-id'),
                                    text: $(this).text().trim()
                                };
                            }).get();

                            console.log('Ordine finale dopo spostamento:', currentOrder);
                        }
                    });
                }
            },

            // Inizializzazione degli autori
            initializeAuthors() {
                this.authors = [{
                    id: '0',
                    name: $('#created-checkbox').attr('tag')
                }];
                this.initializeExistingCheckboxes();
            },

            // Setup dei listener per gli eventi
            setupEventListeners() {
                this.setupSearchHandler();
                this.setupTemplateHandler();
                this.setupAuthorModal();
            },

            // Setup del gestore di ricerca
            setupSearchHandler() {
                $('#search').on('keyup', (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        const $value = $(e.target).val();

                        $.ajax({
                            type: 'get',
                            url: '/authors/search',
                            data: {'search': $value},
                            success: (data) => {
                                $('.authors-checkbox').html(data);
                            }
                        });
                    }
                });
            },

            // Setup del gestore dei template
            setupTemplateHandler() {
                $('#template-selected').on('change', function () {
                    const templateId = $(this).val();
                    if (!templateId) return;

                    $('#template').val(templateId);

                    $.getJSON(`/templates/${templateId}`, (jsonData) => {
                        $('#textarea-section').empty();
                        const fields = JSON.parse(jsonData.fields);

                        fields.forEach((value, key) => {
                            const fieldHtml = `
                        <div class="form-group">
                            <label>${value.name}</label>
                            <textarea name="field_${key + 1}"
                                     id="field_${key + 1}"
                                     rows="10"
                                     cols="80"
                                     form="regForm">
                            </textarea>
                        </div>`;

                            $('#textarea-section').append(fieldHtml);
                            CKEDITOR.replace(`field_${key + 1}`, options);
                        });
                    });
                });
            },

            // Inizializzazione dei checkbox esistenti
            initializeExistingCheckboxes() {
                document.querySelectorAll('.authors-checkbox input[type="checkbox"]').forEach(checkbox => {
                    this.initializeAuthorCheckbox(checkbox);
                });
            },

            // Inizializzazione di un singolo checkbox autore
            initializeAuthorCheckbox(checkbox) {
                $(checkbox).on('change', (e) => {
                    const $checkbox = $(e.target);
                    const authorId = $checkbox.data('author-id');
                    const authorName = $checkbox.data('author-name');

                    if ($checkbox.is(':checked')) {
                        this.addAuthorToSortableList({
                            id: authorId,
                            name: authorName
                        });
                    } else {
                        $(`#selectedAuthors li[data-author-id="${authorId}"]`).remove();
                    }

                    this.updateAuthorsOrder();
                });
            },

            // Gestione del form modale
            setupAuthorModal() {
                const $addAuthorModal = $('#addAuthorModal');
                const $authorFormError = $('#authorFormError');
                const $saveAuthorBtn = $('#saveAuthorBtn');
                const $addNewAuthorBtn = $('#addNewAuthorBtn');

                if ($addNewAuthorBtn.length) {
                    $addNewAuthorBtn.on('click', () => {
                        $('#firstname, #lastname, #email').val('');
                        $authorFormError.hide();
                        $addAuthorModal.modal('show');
                    });
                }

                if ($saveAuthorBtn.length) {
                    $saveAuthorBtn.on('click', () => {
                        this.handleNewAuthorSubmission($addAuthorModal, $saveAuthorBtn);
                    });
                }
            },

            // Gestione della validazione del form modale
            validateAuthorForm() {
                const firstname = $('#firstname').val();
                const lastname = $('#lastname').val();
                const email = $('#email').val();
                let valid = true;
                let errors = [];

                if (!firstname) {
                    errors.push('First Name');
                    $('#firstname').addClass('invalid');
                    valid = false;
                }

                if (!lastname) {
                    errors.push('Last Name');
                    $('#lastname').addClass('invalid');
                    valid = false;
                }

                if (!email) {
                    errors.push('Email');
                    $('#email').addClass('invalid');
                    valid = false;
                } else {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        errors.push('Email (invalid format)');
                        $('#email').addClass('invalid');
                        valid = false;
                    }
                }

                if (!valid) {
                    this.showAlert('danger', 'Please fill in all required fields: ' + errors.join(', '));
                }

                return valid;
            },

            // Gestione della sottomissione di un nuovo autore
            handleNewAuthorSubmission($modal, $btn) {
                if (!this.validateAuthorForm()) return;

                const formData = {
                    firstname: $('#firstname').val(),
                    lastname: $('#lastname').val(),
                    email: $('#email').val(),
                    _token: $('input[name="_token"]').val()
                };

                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: '/admin/authors/check-exists',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: (response) => {
                        if (response.exists) {
                            this.handleExistingAuthorResponse(response, $modal, $btn, originalText);
                        } else {
                            this.createNewAuthor(formData, $btn, originalText, $modal);
                        }
                    },
                    error: (xhr) => {
                        this.handleAjaxError(xhr);
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            },

            // Gestione della risposta per autore esistente
            handleExistingAuthorResponse(response, $modal, $btn, originalText) {
                // Verifichiamo prima se l'autore è già presente nei checkbox
                let existingCheckbox = $(`.authors-checkbox input[value="${response.author.id}"]`);

                // Verifichiamo se l'autore è già presente nella lista sortable
                let existingInSortable = $(`#selectedAuthors li[data-author-id="${response.author.id}"]`).length > 0;

                if (existingCheckbox.length > 0) {
                    if (existingCheckbox.prop('checked') && existingInSortable) {
                        this.showAlert('warning', 'This author is already in your co-authors list');
                    } else if (!existingCheckbox.prop('checked')) {
                        existingCheckbox.prop('checked', true).trigger('change');
                        this.showAlert('success', 'Author added to your co-authors list');
                    }
                } else {
                    this.addAuthorToList(response.author);
                    this.showAlert('success', 'New author added to your co-authors list');
                }

                $modal.modal('hide');
                $btn.prop('disabled', false).text(originalText);
            },

            // Creazione di un nuovo autore
            createNewAuthor(formData, $btn, originalText, $modal) {
                $.ajax({
                    url: '/admin/authors',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: (response) => {
                        if (response.success) {
                            $modal.modal('hide');
                            if (response.author) {
                                let existingCheckbox = $(`.authors-checkbox input[data-author-id="${response.author.id}"]`);
                                if (existingCheckbox.length === 0) {
                                    this.addAuthorToList(response.author);
                                } else if (!existingCheckbox.prop('checked')) {
                                    existingCheckbox.prop('checked', true).trigger('change');
                                }
                                this.showAlert(response.exists ? 'warning' : 'success', response.message);
                            }
                        } else {
                            this.showAlert('danger', response.message || 'Error processing author');
                        }
                    },
                    error: (xhr) => this.handleAjaxError(xhr),
                    complete: () => $btn.prop('disabled', false).text(originalText)
                });
            },

            // Aggiunta di un autore alla lista
            addAuthorToList(author) {
                if (!author?.id || !author?.firstname || !author?.lastname) {
                    console.error('Invalid author data:', author);
                    this.showAlert('danger', 'Invalid author data received');
                    return;
                }

                const checkboxHtml = `
            <div class="item col-md-6 col-xs-6 mb-3">
                <div class="form-check authors-checkbox">
                    <input type="checkbox"
                           id="author_${author.id}"
                           name="authors[]"
                           value="${author.id}"
                           data-author-id="${author.id}"
                           data-author-name="${author.firstname} ${author.lastname}">
                    <label class="form-check-label" for="author_${author.id}">
                        ${author.firstname} ${author.lastname}
                    </label>
                </div>
            </div>
        `;

                const $authorsContainer = $('.authors-checkbox').first().closest('.form-group.row');
                $authorsContainer.append(checkboxHtml);

                const $newCheckbox = $(`input[data-author-id="${author.id}"]`);
                if ($newCheckbox.length) {
                    this.initializeAuthorCheckbox($newCheckbox[0]);
                    $newCheckbox.prop('checked', true).trigger('change');
                }
            },

            // Aggiunta di un autore alla lista ordinabile
            addAuthorToSortableList(author) {
                if ($(`#selectedAuthors li[data-author-id="${author.id}"]`).length) return;

                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.authorId = author.id;

                // Determiniamo se l'autore è il submitter (id = 0)
                const isSubmitter = author.id === '0';

                if (isSubmitter) {
                    li.className += ' is-submitter';
                }

                li.innerHTML = `
                    ${author.name}
                    ${isSubmitter ? '<span class="badge badge-primary ml-2">Submitter</span>' : ''}
                    <i class="fas fa-grip-lines float-right"></i>
                `;

                document.getElementById('selectedAuthors').appendChild(li);
                this.updateAuthorsOrder();
            },

            // Aggiornamento dell'ordine degli autori
            updateAuthorsOrder() {
                const orderedAuthors = [];
                let submitterPosition = 1;

                const authorElements = $('#selectedAuthors li').map(function (index) {
                    return {
                        id: $(this).data('author-id').toString(),
                        text: $(this).text().trim(),
                        position: index + 1
                    };
                }).get();

                const submitterElement = authorElements.find(element => element.id === '0');
                if (submitterElement) {
                    submitterPosition = submitterElement.position;
                }

                authorElements.forEach(element => {
                    if (element.id !== '0') {
                        orderedAuthors.push(element.id);
                    }
                });

                $('#coauthors').val(orderedAuthors.join(','));
                $('#submitter_position').val(submitterPosition);

                console.log('Debug dell\'ordine degli autori:', {
                    authorElements,
                    submitterElement,
                    submitterPosition,
                    orderedAuthors
                });
            },

            // Gestione degli errori AJAX
            handleAjaxError(xhr) {
                let errorMessage = 'An error occurred. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 405) {
                    console.error('Route method not allowed. Check route configuration.');
                    errorMessage = 'Server configuration error. Please contact administrator.';
                } else if (xhr.status === 500) {
                    const response = xhr.responseJSON;
                    errorMessage = response?.message || 'An error occurred while processing your request';
                }

                this.showAlert('danger', errorMessage);
                $('#authorFormError').html(errorMessage).show();

                console.error('Ajax Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: xhr.responseJSON
                });
            },

            // ... continua dal codice precedente ...

            // Visualizzazione degli alert
            showAlert(type, message) {
                $('.alert').remove();

                const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

                $('.card-body').first().prepend(alertHtml);

                setTimeout(() => {
                    $('.alert').fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            },

            // Gestione delle tab
            showTab(n) {
                const x = document.getElementsByClassName("tab");
                x[n].style.display = "block";

                if (n == 0) {
                    document.getElementById("prevBtn").style.display = "none";
                } else {
                    document.getElementById("prevBtn").style.display = "inline";
                }

                if (n == (x.length - 1)) {
                    document.getElementById("nextBtn").innerHTML = "Save Draft";
                    document.getElementById('error').innerHTML = '';
                    document.getElementById("divSubmit").innerHTML = '<button type="button" id="submBtn" onclick="ManuscriptEditor.validateFormReview()" class="btn btn-danger">Submit For Review</button>';
                } else {
                    document.getElementById("nextBtn").innerHTML = "Next";
                    document.getElementById("divSubmit").innerHTML = '';
                }

                this.fixStepIndicator(n);
            },

            // Navigazione tra le tab
            nextPrev(n) {
                const x = document.getElementsByClassName("tab");

                if (n == 1 && !this.validateForm()) return false;

                x[this.currentTab].style.display = "none";
                this.currentTab = this.currentTab + n;

                if (this.currentTab >= x.length) {
                    $('#modalSave').modal('show');
                    document.getElementById("prevBtn").style.display = "none";
                    document.getElementById('loader').style.display = "block";
                    document.getElementById("prevBtn").style.display = "none";
                    document.getElementById("nextBtn").style.display = "none";
                    document.getElementById("submBtn").style.display = "none";

                    document.getElementById('source').value = 'save';

                    setTimeout(() => {
                        document.getElementById("regForm").submit();
                    }, 3000);

                    return false;
                }

                this.showTab(this.currentTab);
            },

            // Validazione del form
            validateForm() {
                const x = document.getElementsByClassName("tab");
                let valid = true;

                const template = document.getElementById("template-selected");
                const title = document.getElementById("title");

                if (!template.value || !title.value) {
                    valid = false;
                }

                if (!template.value) {
                    template.className += ' invalid';
                } else {
                    template.classList.remove('invalid');
                }

                if (!title.value) {
                    title.className += ' invalid';
                } else {
                    title.classList.remove('invalid');
                }

                if (valid) {
                    document.getElementsByClassName("step")[this.currentTab].className += " finish";
                }

                return valid;
            },

            // Validazione del form per il review
            validateFormReview() {
                const form = document.getElementById('regForm');
                const textareas = form.getElementsByTagName("textarea");
                let valid = true;

                // Filtraggio degli input da validare
                const inputsToValidate = Array.from(form.getElementsByTagName("input"))
                    .filter(input => {
                        const excludedIds = ["pdf", "coauthors", "firstname", "lastname", "email", "source", "submitter_position"];
                        return !excludedIds.includes(input.id) && !input.id.startsWith('author_');
                    });

                // Validazione textarea
                Array.from(textareas).forEach(textarea => {
                    const id = textarea.getAttribute('id');
                    if (CKEDITOR.instances[id].getData() === "") {
                        textarea.className += " invalid";
                        valid = false;
                    } else {
                        textarea.classList.remove("invalid");
                    }
                });

                // Validazione input
                inputsToValidate.forEach(input => {
                    if (input.value === "") {
                        input.className += " invalid";
                        valid = false;
                    } else {
                        input.classList.remove("invalid");
                    }
                });

                if (!valid) {
                    document.getElementById('error').innerHTML = '<p class="text-danger">Please fill in all required fields</p>';
                } else {
                    document.getElementById('error').innerHTML = '';
                    document.getElementById('post_state').value = 2;
                    $('#modalSubmit').modal('show');

                    $('#confirmSubmit').off('click').on('click', () => {
                        document.getElementById("regForm").submit();
                    });
                }
            },

            // Indicatore dei passi del form
            fixStepIndicator(n) {
                const x = document.getElementsByClassName("step");
                for (let i = 0; i < x.length; i++) {
                    x[i].className = x[i].className.replace(" active", "");
                }
                x[n].className += " active";
            }



        }

        // Inizializzazione quando il documento è pronto
        $(document).ready(() => {
            ManuscriptEditor.init();

            // Setup dei button handlers
            const nextBtn = document.getElementById("nextBtn");
            const prevBtn = document.getElementById("prevBtn");

            if (nextBtn) {
                nextBtn.onclick = () => ManuscriptEditor.nextPrev(1);
            }

            if (prevBtn) {
                prevBtn.onclick = () => ManuscriptEditor.nextPrev(-1);
            }
        })


    </script>

@endpush



<style>
    .list-group-item {
        cursor: move;
        user-select: none;
    }

    .list-group-item.sortable-ghost {
        opacity: 0.4;
        background-color: #c8ebfb;
    }

    .list-group-item.sortable-chosen {
        background-color: #e9ecef;
    }

    .list-group-item i.fa-grip-lines {
        color: #999;
        cursor: move;
    }
</style>
