@extends('adminlte::page')

@section('content_header')
@stop

@php
    use App\Models\Author;
    use App\Models\Category;
    use App\Models\Status;use App\Models\Template;
    use App\Models\User;use Illuminate\Support\Facades\Auth;

    $usx = Auth::user();

    $authors= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->get();
    $user = Auth::id();
    $mainaut = User::where('id', $item->created)->first();
    $categories = Category::where('visible',1)->orderBy('name','ASC')->get();
    $aut = explode(",", $item->authors);
    $sup = explode(",", $item->supervisors);
    $statuses = Status::orderBy('id')->get();
    $supervisors = User::whereRoleIs('supervisor')->get();
    $templates = Template::where('active',1)->orderBy('id')->get();
    $template = Template::where('id', $item->template )->first();
    $fields=json_decode($template->fields);


    if($item->state==4) {
        $title = 'Final Manuscript';
    }





@endphp

@section('content')
    <div class="container post-form">
        <div class="row">
            <div class="col-lg-12 margin-tb">
                <div class="card card-mini">
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Edit {{$title}}
                        </h1>
                        <div class="card-action">
                            <a href="{{ route('posts.author') }}">
                                <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="count_fields" value="{{count($fields)}}">
                        {!! Form::model($item, ['method' => 'PATCH','route' => ['posts.update', $item->id], 'enctype' => 'multipart/form-data', 'id'=>'regForm'] ) !!}
                        @csrf
                        <!-- One "tab" for each step in the form: -->
                        <div class="tab">

                            <div class="form-group">
                                <div class="col-12"><label>Template</label></div>
                                <select id="template-selected" name="template" class="form-control">
                                    <option value="" data-type="">Choose</option>
                                    @foreach($templates as $template)
                                        <option value="{{$template->id}}"
                                                data-type="{{$template->id}}" {{$item->template == $template->id ? 'selected="selected"' : ''}}>
                                            {{$template->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Title:</label>
                                {!! Form::text('title', null, array('placeholder' => 'Title','class' => 'form-control',  'oninput'=>"this.className = ''")) !!}
                            </div>

                            <div class="form-group row" id="authors-section">
                                <div class="col-8"><label>List of Authors</label></div>
                                <div class="col-4 text-right">

                                    <button type="button" class="btn btn-sm btn-primary" id="addNewAuthorBtn">
                                        <i class="fas fa-plus"></i> Add New Co-Author
                                    </button>
                                </div>

                                <div class="item col-md-6 col-xs-6 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" checked disabled id="created-checkbox"
                                               tag="{{$mainaut->name}} {{$mainaut->surname}}">
                                        <label class="form-check-label"
                                               for="exampleCheck1">{{$mainaut->name}} {{$mainaut->surname}}</label>
                                    </div>
                                </div>
                                @foreach ($authors as $author)

                                    <div class="item col-md-6 col-xs-6 mb-3">
                                        <div class="form-check authors-checkbox">
                                            <input type="checkbox" id="{{$author->id}}"
                                                   name="authors[]" tag="{{$author->firstname}} {{$author->lastname}}"
                                                   value="{{$author->id}}" {{ in_array($author->id, $aut) ? 'checked' : ''}}>
                                            <label class="form-check-label"
                                                   for="exampleCheck1">{{$author->firstname}} {{$author->lastname}}</label>
                                        </div>
                                    </div>

                                @endforeach

                                <div class="col-12">
                                    <div id="author_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Selected Authors Order</div>
                                    <div class="card-body">
                                        <ul id="selectedAuthors" class="list-group">
                                            <li class="list-group-item" data-author-id="0">
                                                {{$mainaut->name}} {{$mainaut->surname}}
                                                <i class="fas fa-grip-lines float-right"></i>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>



                            <div class="form-group">

                                <div class="col-12"><label>Topic</label></div>

                                <select id="items-selected" name="category" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}"
                                                data-type="{{$category->id}}" {{$item->category == $category->id ? 'selected="selected"' : ''}}>
                                            {{$category->name}}
                                        </option>
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


                        <div class="tab" id="textarea-section">
                            @foreach($fields as $key => $value)
                                <div class="form-group">
                                    <label>{{$value->name}}</label>
                                    {!! Form::textarea('field_'.($key+1), null, array('placeholder' => 'Intro','class' => 'form-control', 'id' => 'field_'.($key+1), 'rows'=>10, 'cols'=>80)) !!}
                                </div>
                            @endforeach

                        </div>


                        <div class="tab">
                            <div class="form-group">
                                <label>
                                    Keywords:
                                </label>
                                {!! Form::text('tags', null, array('placeholder' => 'Keywords separated by comma','class' => 'form-control',  'oninput'=>"this.className = ''")) !!}
                            </div>
                            <div class="form-group imageUpload" id="anonymus_pdf_section">
                                <label for="image">Upload Anonymus PDF</label>
                                <div class="note">

                                    <p class="small text-bold">When you upload the document, please be sure the names of
                                        the authors ARE NOT indicated.</p>
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


                            <div class="form-group imageUpload" id="definitive_pdf_section">
                                <label for="image">Upload Final PDF</label>
                                <div class="note">

                                    <p class="small text-bold">When you upload the document, please be sure the names of
                                        the authors ARE indicated.</p>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="definitive_pdf" data-input="doc_pdf" data-preview="cover_preview"
                                           class="btn btn-secondary">
                                            <i class="fa fa-picture-o"></i> Choose
                                        </a>
                                        </span>
                                    {!! Form::text('definitive_pdf', null, array('placeholder' => 'Upload and select the file from File Manager','class' => 'form-control file-src','id' => 'doc_pdf')) !!}

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

                            <div style="float: right" id="divSubmit"></div>
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

                        <div class="modal fade" id="modalDefinitive" tabindex="-1" role="dialog"
                             aria-labelledby="modalSubmitLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalSubmitLabel">Submit Final Manuscript</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure to <b>submit the final manuscript?</b> By doing so, you will no longer be able to modify it.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                        </button>
                                        <button type="button" class="btn btn-primary" id="definitiveSubmit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{ Form::hidden('created', $user) }}
                        {{ Form::hidden('edit', $user) }}
                        {{ Form::hidden('state', $item->state, array('id'=>'post_state')) }}
                        {{ Form::hidden('coauthors', $item->authors, array('id'=>'coauthors')) }}
                        {{ Form::hidden('submitter_position', $item->submitter_position ?? 1, array('id'=>'submitter_position')) }}





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

    <script src="../../../ckeditor/ckeditor.js"></script>

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

        const ManuscriptEditor = {
            // Variabili di stato
            currentTab: 0,
            authors: [],
            state: null,

            // Inizializzazione principale
            init() {
                this.state = $('input[name=state]').val();
                this.initializeUI();
                this.initializeAuthors();
                this.setupEventListeners();
                this.showTab(this.currentTab);
            },

            // Inizializzazione dell'interfaccia utente
            initializeUI() {
                this.initializeCKEditor();
                this.initializeFileManager();
                this.handleStateBasedUI();
            },

            // Inizializzazione di CKEditor per tutte le textarea
            initializeCKEditor() {
                const textareas = document.getElementsByTagName("textarea");
                Array.from(textareas).forEach(textarea => {
                    CKEDITOR.replace(textarea.getAttribute('id'), options);
                });
            },

            // Inizializzazione del file manager
            initializeFileManager() {
                $('#pdf').filemanager('file', '', false);
                $('#definitive_pdf').filemanager('file', '', false);
            },

            // Gestione della UI basata sullo stato
            handleStateBasedUI() {
                if (this.state == 4) {
                    $('#template-selected').prop('disabled', 'disabled');
                    $('input[name=title]').attr('readonly', true);
                    $('#authors-section').hide();
                    $('#anonymus_pdf_section').hide();
                } else {
                    $('#definitive_pdf_section').hide();
                }
            },

            // Gestione degli autori
            initializeAuthors() {
                // Inizializzazione degli autori
                this.authors = [];
                this.loadExistingAuthors();
                this.initializeSortableList();
            },

            // Caricamento degli autori esistenti
            loadExistingAuthors() {
                // Prendiamo il valore di coauthors dal campo nascosto
                let inputAuthors = $('input[name=coauthors]').val();
                // Convertiamo la stringa in array, se vuota usiamo array vuoto
                let arrayAuthors = inputAuthors ? inputAuthors.split(",") : [];

                // Prendiamo la posizione del submitter dal database
                const submitterPosition = parseInt($('#submitter_position').val()) || 1;
                console.log('Posizione del submitter da database:', submitterPosition);

                // Array temporaneo per costruire l'ordine corretto
                let orderedAuthors = [];

                // Primo passo: carichiamo tutti i co-autori selezionati nell'ordine corretto
                $("input[name='authors[]']:checked").each((_, checkbox) => {
                    const $checkbox = $(checkbox);
                    const authorId = $checkbox.attr("id");
                    // Verifichiamo che l'autore sia presente nell'ordine salvato
                    if (arrayAuthors.includes(authorId)) {
                        orderedAuthors.push({
                            id: authorId,
                            name: $checkbox.attr("tag")
                        });
                    }
                });

                // Ordiniamo i co-autori secondo l'ordine salvato in arrayAuthors
                orderedAuthors.sort((a, b) => {
                    return arrayAuthors.indexOf(a.id) - arrayAuthors.indexOf(b.id);
                });

                // Creiamo l'oggetto submitter
                const submitter = {
                    id: '0',
                    name: $('#created-checkbox').attr('tag')
                };

                // Inseriamo il submitter nella posizione corretta
                // Se la posizione è maggiore della lunghezza dell'array, lo mettiamo in fondo
                const insertPosition = Math.min(submitterPosition - 1, orderedAuthors.length);
                orderedAuthors.splice(insertPosition, 0, submitter);

                // Aggiorniamo l'array authors dell'oggetto
                this.authors = orderedAuthors;

                // Puliamo la lista sortable esistente
                $('#selectedAuthors').empty();

                // Ricostruiamo la lista sortable con il nuovo ordine
                this.authors.forEach(author => {
                    this.addAuthorToSortableList(author);
                });

                console.log('Ordine finale degli autori:', this.authors.map(a => ({id: a.id, name: a.name})));
            },

            // Inizializzazione della lista ordinabile
            initializeSortableList() {
                const selectedAuthorsList = document.getElementById('selectedAuthors');
                if (selectedAuthorsList) {
                    new Sortable(selectedAuthorsList, {
                        animation: 150,
                        handle: '.fa-grip-lines',
                        onEnd: (evt) => {
                            this.updateAuthorsOrder();
                        }
                    });
                }
            },

            // Setup degli event listeners
            setupEventListeners() {
                this.setupAuthorCheckboxes();
                this.setupTemplateChange();
                this.setupAuthorModal();
            },

            // Setup degli eventi per i checkbox degli autori
            setupAuthorCheckboxes() {
                $('.authors-checkbox input').on('change', (e) => {
                    const $checkbox = $(e.target);
                    const author = {
                        id: $checkbox.attr("id"),
                        name: $checkbox.attr("tag")
                    };

                    if ($checkbox.is(":checked")) {
                        this.addAuthor(author);
                    } else {
                        this.removeAuthor(author.id);
                    }
                });
            },

            // Setup del cambio template
            setupTemplateChange() {
                $('#template-selected').on('change', function() {
                    const templateId = $(this).val();
                    if (!templateId) return;

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

            // Gestione degli autori
            addAuthor(author) {
                this.authors.push(author);
                this.updateAuthorsList();
                this.addAuthorToSortableList(author);
            },

            removeAuthor(authorId) {
                const index = this.authors.findIndex(x => x.id === authorId);
                if (index > -1) {
                    this.authors.splice(index, 1);
                    this.updateAuthorsList();
                    $(`#selectedAuthors li[data-author-id="${authorId}"]`).remove();
                }
            },

            // Aggiornamento della lista degli autori
            updateAuthorsList() {
                let names = '';
                let ids = [];

                this.authors.forEach((author, i) => {
                    const apix = i === 0 ? '' : ' - ';
                    names += apix + `<span>${author.name}</span>`;

                    if (author.id !== '0') {
                        ids.push(author.id);
                    }
                });

                $('.co-authors').html(`<p>${names}</p>`);
                $("input[name=coauthors]").val(ids.join(','));
            },

            // Aggiunta di un autore alla lista ordinabile
            addAuthorToSortableList(author) {
                if (!author || $(`#selectedAuthors li[data-author-id="${author.id}"]`).length) {
                    return;
                }

                const isSubmitter = author.id === '0';
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.authorId = author.id;

                li.innerHTML = `
            ${author.name}
            ${isSubmitter ? '<span class="badge badge-primary ml-2">Submitter</span>' : ''}
            <i class="fas fa-grip-lines float-right"></i>
        `;

                document.getElementById('selectedAuthors').appendChild(li);
            },

            // Aggiornamento dell'ordine degli autori
            updateAuthorsOrder() {
                const orderedAuthors = [];
                let submitterPosition = 1;

                // Iteriamo attraverso la lista ordinabile per ottenere l'ordine corrente
                $('#selectedAuthors li').each(function(index) {
                    const authorId = $(this).data('author-id').toString();
                    if (authorId === '0') {
                        submitterPosition = index + 1;
                    } else {
                        orderedAuthors.push(authorId);
                    }
                });

                // Aggiorniamo i campi nascosti con i nuovi valori
                $('#coauthors').val(orderedAuthors.join(','));
                $('#submitter_position').val(submitterPosition);
            },

            setupAuthorModal() {
                const $addAuthorModal = $('#addAuthorModal');
                const $authorFormError = $('#authorFormError');
                const $saveAuthorBtn = $('#saveAuthorBtn');
                const $addNewAuthorBtn = $('#addNewAuthorBtn');

                // Handler per l'apertura della modale
                if ($addNewAuthorBtn.length) {
                    $addNewAuthorBtn.on('click', () => {
                        $('#firstname, #lastname, #email').val('');
                        $authorFormError.hide();
                        $addAuthorModal.modal('show');
                    });
                }

                // Handler per il salvataggio del nuovo autore
                if ($saveAuthorBtn.length) {
                    $saveAuthorBtn.on('click', () => {
                        this.handleNewAuthorSubmission($addAuthorModal, $saveAuthorBtn);
                    });
                }
            },

            // Gestione della sottomissione di un nuovo autore
            handleNewAuthorSubmission($modal, $btn) {
                const formData = this.validateAndGetFormData();
                if (!formData) return;

                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Saving...');

                // Prima controlliamo se l'autore esiste già
                this.checkExistingAuthor(formData, $modal, $btn, originalText);
            },

            // Validazione dei dati del form
            validateAndGetFormData() {
                const firstname = $('#firstname').val();
                const lastname = $('#lastname').val();
                const email = $('#email').val();

                // Verifica campi vuoti
                if (!firstname || !lastname || !email) {
                    this.showAlert('danger', 'Please fill in all fields to add a new co-author');
                    return null;
                }

                // Validazione email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    this.showAlert('danger', 'Please enter a valid email address');
                    return null;
                }

                return {
                    firstname,
                    lastname,
                    email,
                    _token: $('input[name="_token"]').val()
                };
            },

            // Controllo dell'esistenza dell'autore
            checkExistingAuthor(formData, $modal, $btn, originalText) {
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
                if (response.relationExists) {
                    this.showAlert('warning', response.message);
                } else {
                    this.showAlert('success', response.message);
                }
                this.handleExistingAuthor(response.author);
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
                                const existingCheckbox = $(`.authors-checkbox input[data-author-id="${response.author.id}"]`);

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
                    error: (xhr) => {
                        this.handleAjaxError(xhr);
                    },
                    complete: () => {
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            },

            // Gestione di un autore esistente
            handleExistingAuthor(author) {
                // Verifichiamo prima se l'autore è già presente nei checkbox
                let existingCheckbox = $(`.authors-checkbox input[value="${author.id}"]`);

                // Verifichiamo se l'autore è già presente nella lista sortable
                let existingInSortable = $(`#selectedAuthors li[data-author-id="${author.id}"]`).length > 0;

                if (existingCheckbox.length > 0) {
                    // Se l'autore esiste già come checkbox
                    if (existingCheckbox.prop('checked') && existingInSortable) {
                        // Se è già selezionato e presente nella lista sortable, mostriamo solo un messaggio
                        this.showAlert('warning', 'This author is already in your co-authors list');
                    } else if (!existingCheckbox.prop('checked')) {
                        // Se esiste ma non è selezionato, lo selezioniamo
                        existingCheckbox.prop('checked', true).trigger('change');
                        this.showAlert('success', 'Author added to your co-authors list');
                    }
                } else {
                    // Solo se l'autore non esiste affatto, lo aggiungiamo alla lista
                    this.addAuthorToList(author);
                    this.showAlert('success', 'New author added to your co-authors list');
                }
            },

            // Aggiunta di un autore alla lista
            addAuthorToList(author) {
                if (!author || !author.id || !author.firstname || !author.lastname) {
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
                    // Inizializzazione del nuovo checkbox
                    $newCheckbox.on('change', (e) => {
                        const $checkbox = $(e.target);
                        if ($checkbox.is(':checked')) {
                            this.addAuthorToSortableList({
                                id: author.id,
                                name: `${author.firstname} ${author.lastname}`
                            });
                        } else {
                            $(`#selectedAuthors li[data-author-id="${author.id}"]`).remove();
                        }
                        this.updateAuthorsOrder();
                    });

                    // Selezione automatica del nuovo checkbox
                    $newCheckbox.prop('checked', true);
                    $newCheckbox.trigger('change');
                }
            },

            // Gestione degli errori Ajax
            handleAjaxError(xhr) {
                let errorMessage = 'An error occurred. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                this.showAlert('danger', errorMessage);
                $('#authorFormError').html(errorMessage).show();
            },

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


            validateForm() {
                const x = document.getElementsByClassName("tab");
                const y = x[this.currentTab].getElementsByTagName("input");
                let valid = true;

                const template = document.getElementById("template-selected");
                if (!template.value) {
                    template.className += ' invalid';
                    valid = false;
                } else {
                    template.classList.remove('invalid');
                    valid = true;
                }

                if (valid) {
                    document.getElementsByClassName("step")[this.currentTab].className += " finish";
                }
                return valid;
            },

            showTab(n) {
                const x = document.getElementsByClassName("tab");
                x[n].style.display = "block";

                // Gestione pulsanti Previous/Next
                if (n == 0) {
                    document.getElementById("prevBtn").style.display = "none";
                } else {
                    document.getElementById("prevBtn").style.display = "inline";
                }

                if (n == (x.length - 1)) {
                    document.getElementById("nextBtn").innerHTML = "Save Draft";
                    document.getElementById('error').innerHTML = '';

                    if (this.state == 1) {
                        document.getElementById("divSubmit").innerHTML = '<button type="button" id="submBtn" onclick="ManuscriptEditor.validateFormReview()" class="btn btn-danger">Submit For Review</button>';
                    } else if (this.state == 4) {
                        $('#nextBtn').hide();
                        document.getElementById("divSubmit").innerHTML = '<button type="button" id="submBtn" onclick="ManuscriptEditor.validateFormReview()" class="btn btn-danger">Submit Final Manuscript</button>';
                    }
                } else {
                    document.getElementById("nextBtn").innerHTML = "Next";
                    document.getElementById("divSubmit").innerHTML = '';
                }

                this.fixStepIndicator(n);
            },

            fixStepIndicator(n) {
                const x = document.getElementsByClassName("step");
                for (let i = 0; i < x.length; i++) {
                    x[i].className = x[i].className.replace(" active", "");
                }
                x[n].className += " active";
            },


            nextPrev(n) {
                const x = document.getElementsByClassName("tab");

                // Validazione prima di procedere
                if (n == 1 && !this.validateForm()) return false;

                // Nascondi il tab corrente
                x[this.currentTab].style.display = "none";

                // Incrementa o decrementa il tab corrente
                this.currentTab = this.currentTab + n;

                // Se hai raggiunto la fine del form
                if (this.currentTab >= x.length) {
                    $('#modalSave').modal('show');
                    document.getElementById("prevBtn").style.display = "none";
                    document.getElementById('loader').style.display = "block";
                    document.getElementById("prevBtn").style.display = "none";
                    document.getElementById("nextBtn").style.display = "none";
                    document.getElementById("submBtn").style.display = "none";

                    setTimeout(() => {
                        document.getElementById("regForm").submit();
                    }, 3000);

                    return false;
                }

                // Mostra il tab corrente
                this.showTab(this.currentTab);
            },



            validateFormReview() {
                // Otteniamo tutti gli elementi da validare
                const form = document.getElementById('regForm');
                const textareas = form.getElementsByTagName("textarea");
                const allInputs = form.getElementsByTagName("input");
                let valid = true;

                // Creiamo un oggetto per tracciare i campi non validi
                const invalidFields = {
                    textareas: [],
                    inputs: []
                };

                // Filtriamo gli input escludendo quelli del form autore e altri campi non necessari
                const inputsToValidate = Array.from(allInputs).filter(input => {
                    const excludedIds = [
                        'pdf',
                        'coauthors',
                        'doc_pdf',
                        'firstname',  // Escludiamo i campi del form autore
                        'lastname',   // dalla validazione generale
                        'email',
                        'source',
                        'submitter_position'
                    ];

                    return !excludedIds.includes(input.id) &&
                        !input.id.startsWith('author_');
                });

                // Validazione delle textarea
                Array.from(textareas).forEach(textarea => {
                    const id = textarea.getAttribute('id');
                    const label = textarea.previousElementSibling?.textContent || id;

                    if (CKEDITOR.instances[id].getData() === "") {
                        textarea.className += " invalid";
                        valid = false;
                        invalidFields.textareas.push(label);
                        console.log(`Campo vuoto: ${label}`);
                    } else {
                        textarea.classList.remove("invalid");
                    }
                });

                // Validazione degli input filtrati
                inputsToValidate.forEach(input => {
                    const label = this.findInputLabel(input);

                    if (input.value === "") {
                        input.className += " invalid";
                        valid = false;
                        invalidFields.inputs.push(label);
                        console.log(`Campo vuoto: ${label}`);
                    } else {
                        input.classList.remove("invalid");
                    }
                });

                // Mostra messaggi di errore appropriati
                if (!valid) {
                    this.showDetailedValidationError(invalidFields);
                } else {
                    this.handleValidSubmission();
                }
            },

            findInputLabel(input) {
                // Prima cerchiamo una label collegata tramite "for"
                let label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    return label.textContent.trim();
                }

                // Cerchiamo una label come genitore
                let parentLabel = input.closest('label');
                if (parentLabel) {
                    return parentLabel.textContent.replace(input.value, '').trim();
                }

                // Cerchiamo una label nel gruppo form
                let formGroup = input.closest('.form-group');
                if (formGroup) {
                    label = formGroup.querySelector('label');
                    if (label) {
                        return label.textContent.trim();
                    }
                }

                // Fallback all'ID o nome del campo formattato in modo leggibile
                return input.name
                    ? input.name.replace(/([A-Z])/g, ' $1').toLowerCase()
                    : input.id
                        ? input.id.replace(/([A-Z])/g, ' $1').toLowerCase()
                        : 'Campo sconosciuto';
            },

            validateAuthorForm() {
                const firstname = $('#firstname').val();
                const lastname = $('#lastname').val();
                const email = $('#email').val();
                let valid = true;
                let errors = [];

                // Verifica campi vuoti
                if (!firstname) {
                    errors.push('Nome');
                    $('#firstname').addClass('invalid');
                    valid = false;
                } else {
                    $('#firstname').removeClass('invalid');
                }

                if (!lastname) {
                    errors.push('Cognome');
                    $('#lastname').addClass('invalid');
                    valid = false;
                } else {
                    $('#lastname').removeClass('invalid');
                }

                if (!email) {
                    errors.push('Email');
                    $('#email').addClass('invalid');
                    valid = false;
                } else {
                    // Validazione formato email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        errors.push('Email (formato non valido)');
                        $('#email').addClass('invalid');
                        valid = false;
                    } else {
                        $('#email').removeClass('invalid');
                    }
                }

                // Mostra errori se necessario
                if (!valid) {
                    const errorMessage = 'Per favore compila i seguenti campi: ' + errors.join(', ');
                    this.showAlert('danger', errorMessage);
                    $('#authorFormError').html(errorMessage).show();
                }

                return valid;
            },

// Funzione per mostrare errori dettagliati
            showDetailedValidationError(invalidFields) {
                let errorMessage = '<div class="alert alert-danger">';
                errorMessage += '<p>I seguenti campi sono obbligatori:</p><ul>';

                invalidFields.textareas.forEach(field => {
                    errorMessage += `<li>${field}</li>`;
                });

                invalidFields.inputs.forEach(field => {
                    errorMessage += `<li>${field}</li>`;
                });

                errorMessage += '</ul></div>';
                document.getElementById('error').innerHTML = errorMessage;
            },

            // Filtra gli input da validare in base allo stato
            filterInputsForValidation(inputs) {
                return Array.from(inputs).filter(input => {
                    if (this.state != 4) {
                        return input.id !== "pdf" &&
                            input.id !== "coauthors" &&
                            input.id !== 'doc_pdf';
                    } else {
                        return input.id !== "pdf" &&
                            input.id !== "coauthors";
                    }
                });
            },

            // Mostra il messaggio di errore di validazione
            showValidationError() {
                document.getElementById('error').innerHTML =
                    '<p class="text-danger">please fill in all required fields</p>';
            },

            // Gestisce la sottomissione valida del form
            handleValidSubmission() {
                document.getElementById('error').innerHTML = '';

                if (this.state == 4) {
                    this.handleFinalManuscriptSubmission();
                } else {
                    this.handleReviewSubmission();
                }
            },

            // Gestisce la sottomissione del manoscritto finale
            handleFinalManuscriptSubmission() {
                document.getElementById('post_state').value = 6;
                $('#modalDefinitive').modal('show');

                $('#definitiveSubmit').off('click').on('click', () => {
                    document.getElementById("regForm").submit();
                });
            },

            // Gestisce la sottomissione per review
            handleReviewSubmission() {
                document.getElementById('post_state').value = 2;
                $('#modalSubmit').modal('show');

                $('#confirmSubmit').off('click').on('click', () => {
                    document.getElementById("regForm").submit();
                });
            }

        };

        $(document).ready(() => {
            ManuscriptEditor.init();
            updateButtonHandlers();
        });



        function updateButtonHandlers() {
            const nextBtn = document.getElementById("nextBtn");
            const prevBtn = document.getElementById("prevBtn");

            if (nextBtn) {
                nextBtn.onclick = () => ManuscriptEditor.nextPrev(1);
            }

            if (prevBtn) {
                prevBtn.onclick = () => ManuscriptEditor.nextPrev(-1);
            }
        }






    </script>

@endpush


