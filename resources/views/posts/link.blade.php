@extends('adminlte::page')

@section('content_header')
@stop

@php
    use App\Models\Author;
    use App\Models\Category;
    use App\Models\Status;use App\Models\Template;
    use App\Models\User;use Illuminate\Support\Facades\Auth;

    $usx = Auth::user();

    if ($usx->hasRole('superadministrator|administrator|supervisor')){
    $authors = Author::orderBy('id')->get();
    }

    else {
    $authors = Author::where('user_id', Auth::id())->get();
    }
    $user = Auth::id();
    $mainaut = User::where('id', $item->created)->first();
    $categories = Category::orderBy('id')->get();
    $template = Template::where('active', 1)->first();
    $fields=json_decode($template->fields);
    $aut = explode(",", $item->authors);
    $sup = explode(",", $item->supervisors);
    $statuses = Status::orderBy('id')->get();
    $supervisors = User::whereRoleIs('supervisor')->get();




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
                            <a href="{{ route('posts.index') }}">
                                <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="count_fields" value="{{count($fields)}}">
                    {!! Form::model($item, ['method' => 'PATCH','route' => ['posts.link', $item->id], 'enctype' => 'multipart/form-data', 'id'=>'regForm'] ) !!}
                    @csrf
                    <!-- One "tab" for each step in the form: -->
                        <div class="tab">
                            <div class="form-group">
                                <label>Title:</label>
                                {!! Form::text('title', null, array('placeholder' => 'Title','class' => 'form-control',  'oninput'=>"this.className = ''")) !!}
                            </div>

                            <div class="form-group row">
                                <div class="col-12"><label>Authors</label></div>
                                <div class="item col-md-6 col-xs-6 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" checked disabled>
                                        <label class="form-check-label"
                                               for="exampleCheck1">{{$mainaut->name}} {{$mainaut->surname}}</label>
                                    </div>
                                </div>
                                @foreach ($authors as $author)


                                    <div class="item col-md-6 col-xs-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="{{$author->id}}"
                                                   name="authors[]"
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

                            <div class="form-group">

                                <div class="col-12"><label>Category</label></div>

                                <select id="items-selected" name="category" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}"
                                                data-type="{{$category->id}}" {{$item->category == $category->id ? 'selected="selected"' : ''}}>
                                            {{$category->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            @role('superadministrator|administrator|supervisor')
                            <div class="form-group">

                                <div class="col-12"><label>Status</label></div>

                                <select id="statuses-selected" name="state" class="form-control">
                                    <option value="" data-type="">Choose</option>
                                    @foreach($statuses as $status)
                                        <option value="{{$status->id}}"
                                                data-type="{{$status->id}}" {{$item->state == $status->id ? 'selected="selected"' : ''}}>
                                            {{$status->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole

                            @role('superadministrator|administrator')
                            <div class="form-group row">

                                <div class="col-12"><label>Supervisors</label></div>

                                @foreach ($supervisors as $supervisor)

                                    <div class="item col-md-6 col-xs-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="{{$supervisor->id}}"
                                                   name="authors[]"
                                                   value="{{$supervisor->id}}" {{ in_array($supervisor->id, $sup) ? 'checked' : ''}}>
                                            <label class="form-check-label"
                                                   for="exampleCheck1">{{$supervisor->name}} {{$supervisor->surname}}</label>
                                        </div>
                                    </div>

                                @endforeach

                            </div>

                            @endrole


                        </div>


                        <div class="tab">
                            <div class="form-group">
                                <label>Abstract</label>
                                {!! Form::textarea('abstract', null, array('placeholder' => 'Abstract','class' => 'form-control', 'id' => 'abstract', 'rows'=>10, 'cols'=>80)) !!}
                            </div>

                            <div class="form-group">
                                <label>Intro</label>
                                {!! Form::textarea('intro', null, array('placeholder' => 'Intro','class' => 'form-control', 'id' => 'intro', 'rows'=>10, 'cols'=>80)) !!}
                            </div>
                        </div>

                        <div class="tab">
                            @foreach($fields as $key => $value)
                                <div class="form-group">
                                    <label>{{$value->name}}</label>

                                    {!! Form::textarea('field_'.($key+1), null, array('placeholder' => 'Intro','class' => 'form-control', 'id' => 'field_'.($key+1), 'rows'=>10, 'cols'=>80)) !!}

                                </div>
                            @endforeach

                        </div>

                        <div class="tab">
                            <div class="form-group">
                                <label>Ending</label>
                                {!! Form::textarea('ending', null, array('placeholder' => 'Ending','class' => 'form-control', 'id' => 'ending', 'rows'=>10, 'cols'=>80)) !!}
                            </div>

                            <div class="form-group">
                                <label>Biography</label>
                                {!! Form::textarea('biography', null, array('placeholder' => 'Biography','class' => 'form-control', 'id' => 'biography', 'rows'=>10, 'cols'=>80)) !!}
                            </div>
                        </div>


                        <div class="tab">
                            <div class="form-group">
                                <label>
                                    Tags:
                                </label>
                                {!! Form::text('tags', null, array('placeholder' => 'Tag separated by comma','class' => 'form-control',  'oninput'=>"this.className = ''")) !!}
                            </div>
                            <div class="form-group imageUpload">
                                <label for="image">PDF Document</label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="document" data-input="thumbnail" data-preview="cover_preview"
                                           class="btn btn-secondary">
                                            <i class="fa fa-picture-o"></i> Choose
                                        </a>
                                        </span>
                                    {!! Form::text('pdf', null, array('placeholder' => 'PDF','class' => 'form-control file-src','id' => 'thumbnail')) !!}

                                </div>

                            </div>
                            <!--<button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i> Save
                            </button> -->
                        </div>

                        <div style="display: none" id="loader"><h3>Loading...</h3></div>
                        <div style="overflow:auto;">
                            <div style="float:right;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-primary">
                                    Previous
                                </button>
                                <button type="button" id="nextBtn" onclick="nextPrev(1)" class="btn btn-primary">Next
                                </button>
                            </div>
                        </div>

                        <!-- Circles which indicates the steps of the form: -->
                        <div style="text-align:center;margin-top:40px;">
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                        </div>

                        {{ Form::hidden('template', $template['id']) }}
                        {{ Form::hidden('created', $user) }}
                        {{ Form::hidden('edit', $user) }}


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

    <script src="../../../ckeditor/ckeditor.js"></script>

    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>

    <script>

        var options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
        };

        $(document).ready(function () {

            var fields = $('#count_fields').val();
            console.log(fields);

            var txt = document.getElementsByTagName("textarea");

            for (var i = 0; i < txt.length; i++) {
                var id = txt[i].getAttribute('id');
                CKEDITOR.replace(id, options);
            }

            $('#document').filemanager('file', '', false);
        });

        var currentTab = 0; // Current tab is set to be the first tab (0)
        showTab(currentTab); // Display the current tab

        function showTab(n) {
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
                document.getElementById("nextBtn").innerHTML = "Submit";
            } else {
                document.getElementById("nextBtn").innerHTML = "Next";
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

                document.getElementById('loader').style.display = "block";
                document.getElementById("prevBtn").style.display = "none";
                document.getElementById("nextBtn").style.display = "none";

                //...the form gets submitted:
                document.getElementById("regForm").submit();
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
            z = x[currentTab].getElementsByTagName("textarea");


            var checkbox = document.querySelector('input[name="authors[]"]:checked');
            if (!checkbox) {
                document.getElementById('author_error').innerHTML = '<p class="text-danger">Please select an author</p>'
                valid = false;
            } else {
                document.getElementById('author_error').innerHTML = '';
                valid = true;
            }

            // A loop that checks every input field in the current tab:
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value == "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;
                }

            }

            for (i = 0; i < z.length; i++) {
                var id = z[i].getAttribute('id');

                if (CKEDITOR.instances[id].getData() === "") {
                    z[i].className += " invalid";
                    // and set the current valid status to false:
                    valid = false;
                } else {
                    z[i].classList.remove('invalid');
                }
            }
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
    </script>

@endpush

