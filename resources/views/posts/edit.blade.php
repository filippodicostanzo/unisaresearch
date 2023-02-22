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
                                <div class="col-12"><label>List of Authors</label></div>

                                @if(count($authors)==0)
                                    <div class="col-12 mb-3">Please, add your co-authors by clicking <a
                                            href="../../authors">here</a> or on " My co-authors" on the left before
                                        adding
                                        a manuscript.
                                    </div>
                                @endif

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

                            <div class="form-group row">
                                <div class="col-12"><label>Authors Selected</label></div>
                                <div class="col-12">
                                    <div class="co-authors"></div>
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

    <script>

        var options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}'
        };

        $(document).ready(function () {

            let authors = [{id: '0', name: $('#created-checkbox').attr('tag')}];

            let inputAuthors = $('input[name=coauthors]').val();

            let arrayAuthors = [];

            if (inputAuthors !== "") {
                arrayAuthors = inputAuthors.split(",");
            }

            arrayAuthors.unshift('0');

            let state = $('input[name=state]').val();


            if (state == 4) {
                $('#template-selected').prop('disabled', 'disabled');
                $('input[name=title]').attr('readonly', true);
                $('#authors-section').hide();
                $('#anonymus_pdf_section').hide();

            } else {
                $('#definitive_pdf_section').hide();
            }


            $("input[name='authors[]']").each(function () {

                if ($(this).is(':checked')) {

                    let id = $(this).attr("id");
                    let name = $(this).attr("tag")

                    let obj = {
                        id: id,
                        name: name
                    }

                    authors.push(obj);

                }
            });

            let ids = [];


            authors = arrayAuthors.map((i) => authors.find((j) => j.id === i));

            console.log(authors);


            authors.forEach((el, i) => {
                let apix = i === 0 ? '' : ' - ';
                if (el!=undefined) {
                    $('.co-authors').append(apix + '<span>' + el.name + '</span>');
                }
            })


            $('.authors-checkbox input').on('click', function () {


                let id = $(this).attr("id");
                let name = $(this).attr("tag")


                var obj = {
                    id: id,
                    name: name
                }


                if ($(this).is(":checked")) {
                    authors.push(obj);
                } else {

                    let index = authors.map(x => {
                        return x.id;
                    }).indexOf(id)

                    authors.splice(index, 1);
                }

                let names = '';
                ids = [];

                authors.forEach((obj, i) => {
                        let apix = i === 0 ? '' : ' - ';
                        names += apix + '<span>' + obj.name + '</span>';

                        if (obj.id != 0) {
                            ids.push(obj.id);
                        }
                    }
                );


                $('.co-authors').html('<p>' + names + '</p>');


                $("input[name=coauthors]").val(ids.join(','));

                // $('.co-authors').html('<p>CIAO A TUTTI</p>');


            });

            var fields = $('#count_fields').val();


            var txt = document.getElementsByTagName("textarea");

            for (var i = 0; i < txt.length; i++) {
                var id = txt[i].getAttribute('id');
                CKEDITOR.replace(id, options);
            }

            $('#pdf').filemanager('file', '', false);

            $('#definitive_pdf').filemanager('file', '', false);

            $('#template-selected').change(function () {


                $('#template').val($(this).val());


                $.getJSON("/templates/" + $(this).val(), function (jsonData) {

                    $('#textarea-section').empty();


                    let fields = JSON.parse(jsonData.fields);

                    fields.forEach((value, key) => {

                        $('#textarea-section').append('<div class="form-group"> <label>' + value.name + '</label> <textarea name="field_' + parseInt(key + 1) + '" id="field_' + parseInt(key + 1) + '" rows="10" cols="80" form="regForm"></textarea></div>');
                        CKEDITOR.replace('field_' + parseInt(key + 1), options);
                    })

                })


                //   $('#textarea-section').append('<textarea name="field_21" id="field_21" rows="10" cols="80" class="form-control"></textarea>')


            });

        });

        var currentTab = 0; // Current tab is set to be the first tab (0)
        showTab(currentTab); // Display the current tab

        function showTab(n) {
            let state = $('input[name=state]').val();

            // This function will display the specified tab of the form ...
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            // ... and fix the Previous/Next buttons:
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "inline";
            }
            if (n == (x.length - 1)) {

                document.getElementById("nextBtn").innerHTML = "Save Draft";
                document.getElementById('error').innerHTML = '';

                if (state == 1) {
                    document.getElementById("divSubmit").innerHTML = '<button type="button" id="submBtn" onclick="validateFormReview()" class="btn btn-danger">Submit For Review</button>';
                } else if (state == 4) {
                    $('#nextBtn').hide();
                    document.getElementById("divSubmit").innerHTML = '<button type="button" id="submBtn" onclick="validateFormReview()" class="btn btn-danger">Submit Final Manuscript</button>';
                }

            } else {
                document.getElementById("nextBtn").innerHTML = "Next";
                document.getElementById("divSubmit").innerHTML = '';
            }
            // ... and run a function that displays the correct step indicator:
            fixStepIndicator(n)
        }

        function nextPrev(n) {
            // This function will figure out which tab to display
            var x = document.getElementsByClassName("tab");
            // Exit the function if any field in the current tab is invalid:
            if (n == 1 && !validateForm()) return false;


            // Hide the current tab:
            x[currentTab].style.display = "none";
            // Increase or decrease the current tab by 1:
            currentTab = currentTab + n;
            // if you have reached the end of the form... :
            if (currentTab >= x.length) {

                $('#modalSave').modal('show');
                document.getElementById("prevBtn").style.display = "none";
                document.getElementById('loader').style.display = "block";
                document.getElementById("prevBtn").style.display = "none";
                document.getElementById("nextBtn").style.display = "none";
                document.getElementById("submBtn").style.display = "none";

                setTimeout(() => {
                    document.getElementById("regForm").submit();
                }, 3000)
                //...the form gets submitted:


                return false;
            }
            // Otherwise, display the correct tab:
            showTab(currentTab);
        }

        function validateForm() {
            // This function deals with validation of the form fields
            var x, y, i, z, k, valid = true;
            x = document.getElementsByClassName("tab");
            y = x[currentTab].getElementsByTagName("input");
            //z = x[currentTab].getElementsByTagName("textarea");

            var template = document.getElementById("template-selected");


            if (!template.value) {
                template.className += ' invalid'
                valid = false;
            } else {
                template.classList.remove('invalid');
                valid = true;
            }
            /*
            var checkbox = document.querySelector('input[name="authors[]"]:checked');
            if (!checkbox) {
                document.getElementById('author_error').innerHTML = '<p class="text-danger">Please select an author</p>'
                valid = false;
            } else {
                document.getElementById('author_error').innerHTML = '';
                valid = true;
            }
            */

            // A loop that checks every input field in the current tab:
            /*
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value == "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;
                }

            }*/

            /*
            for (i = 0; i < z.length; i++) {
                var id = z[i].getAttribute('id');

                if (CKEDITOR.instances[id].getData() === "") {
                    z[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;
                } else {
                    z[i].classList.remove('invalid');
                }
            }*/
            // If the valid status is true, mark the step as finished and valid:
            if (valid) {
                document.getElementsByClassName("step")[currentTab].className += " finish";
            }
            return valid; // return the valid status
        }

        function fixStepIndicator(n) {
            // This function removes the "active" class of all steps...
            var i, x = document.getElementsByClassName("step");
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(" active", "");
            }
            //... and adds the "active" class to the current step:
            x[n].className += " active";
        }

        function validateFormReview() {
            let input_textarea = document.getElementById('regForm').getElementsByTagName("textarea");
            let input_field = document.getElementById('regForm').getElementsByTagName("input");

            let valid = true;
            let i;

            var inputsWeActuallyWant = [];
            let state = $('input[name=state]').val();
            console.log(input_field);
            for (var j = (input_field.length - 1); j >= 0; j--) {

                if (state != 4) {

                    if (input_field[j].id !== "pdf" && input_field[j].id !== "coauthors" && input_field[j].id !== 'doc_pdf') {
                        inputsWeActuallyWant.push(input_field[j]);
                    }
                } else {
                    if (input_field[j].id !== "pdf" && input_field[j].id !== "coauthors") {
                        inputsWeActuallyWant.push(input_field[j]);
                    }
                }
            }

            input_field = inputsWeActuallyWant;

            console.log(input_field);


            for (i = 0; i < input_textarea.length; i++) {
                var id = input_textarea[i].getAttribute('id');
                // If a field is empty...
                if (CKEDITOR.instances[id].getData() === "") {
                    console.log('sono nella text area');
                    // add an "invalid" class to the field:
                    input_textarea[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;
                } else {
                    input_textarea[i].classList.remove("invalid");
                }

            }

            for (i = 0; i < input_field.length; i++) {
                // If a field is empty...
                if (input_field[i].value === "") {
                    // add an "invalid" class to the field:
                    input_field[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;

                } else {
                    input_field[i].classList.remove("invalid");
                }
            }

            if (!valid) {
                document.getElementById('error').innerHTML = '<p class="text-danger">please fill in all required fields</p>'
            } else {
                let state = $('input[name=state]').val();
                document.getElementById('error').innerHTML = '';
                if (state == 4) {
                    document.getElementById('post_state').value = 6;
                    $('#modalDefinitive').modal('show');

                    $('#definitiveSubmit').on('click', function () {
                        document.getElementById("regForm").submit();
                    })

                } else {
                    document.getElementById('post_state').value = 2;
                    $('#modalSubmit').modal('show');
                    $('#confirmSubmit').on('click', function () {
                        document.getElementById("regForm").submit();
                    })
                }



            }

        }


    </script>

@endpush


