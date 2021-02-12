@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content_header')
@stop

@php
    use App\Models\Author;
    use App\Models\Edition;
    use App\Models\Post;
    use App\Models\Category;
    use App\Models\Status;use App\Models\Template;
    use App\Models\User;use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    $usx = Auth::user();

    if ($usx->hasRole('superadministrator|administrator|supervisor')){
    $authors = Author::orderBy('id')->get();
    }



    else {
    $authors = Author::where('user_id', Auth::id())->get();
    }

    $autx = $item->authors()->get();
    $flx = json_decode($item->template_fk->fields);
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
    <div class="post-form">
        <div class="row">

            <div class="col-lg-12 margin-tb">
                <div class="card">

                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Assign Paper {{$item->title}} to Reviewer <span class="state-label"
                                                                            style="background-color:{{$item->state_fk->color}}">{{$item->state_fk->name}}</span>
                        </h1>

                        <div class="card-action">
                            <a href="{{ route('posts.index') }}">
                                <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="count_fields" value="{{count($fields)}}">

                        <!-- One "tab" for each step in the form: -->


                        <div class="row pt-3">
                            <div class="col-md-6 col-sm-12">
                                <span class="text-bold">Submitted By: </span>
                                {{$item->user_fk->name}} {{$item->user_fk->surname}}
                            </div>

                            @if (count($autx)>0)
                                <div class="col-md-6 col-sm-12">
                                    <span class="text-bold">Co-Authors: </span>

                                    @foreach ($autx as $author)
                                        {{$author->firstname}} {{$author->lastname}}
                                        @if(!$loop->last) - @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="col-md-6 col-sm-12">
                                <span class="text-bold">Template: </span>
                                {{$item->template_fk->name}}
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <span class="text-bold">Topic: </span>
                                {{$item->category_fk->name}}
                            </div>
                        </div>
                    </div>
                </div>


                @foreach($flx as $key => $value)
                    <div class="card">
                        <div class="card-header">
                            <h1 class="m0 text-dark card-title text-xl">
                                {{$value->name}}
                            </h1>
                        </div>

                        @if($key===0)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_1!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===1)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_2!!}

                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===2)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_3!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===3)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_4!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===4)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_5!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===5)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_6!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===6)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_7!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===7)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_8!!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($key===8)
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-12 col-sm-12">
                                        {!! $item->field_9!!}
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach


                <div class="card">
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Extra
                        </h1>
                    </div>

                    <div class="card-body">
                        <div class="row pt-3">
                            <div class="col-md-6 col-sm-12">
                                <span class="text-bold"> Tags: </span>
                                {{$item->tags}}
                            </div>

                            @if($item->pdf!='')
                                <div class="col-md-6 col-sm-12">
                                    <span class="text-bold"> Pdf Document: </span>
                                    <a href="{{$item->pdf}}" class="btn btn-primary">Download</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>


                @role('superadministrator|administrator|supervisor')

                <div class="card">
                    {!! Form::model($item, ['method' => 'PATCH','route' => ['posts.link', $item->id], 'enctype' => 'multipart/form-data', 'id'=>'regForm'] ) !!}
                    @csrf
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Change Status & Assign To Reviewers
                        </h1>
                    </div>
                    <div class="card-body">
                        <div class="row pt-3">
                            <div class="col-12"><label>Status</label></div>

                            <select id="statuses-selected" name="state" class="form-control">
                                <option value="" data-type="">Choose</option>
                                @foreach($statuses as $status)
                                    @if($status->id !='1')
                                        <option value="{{$status->id}}"
                                                data-type="{{$status->id}}" {{$item->state == $status->id ? 'selected="selected"' : ''}}>
                                            {{$status->name}}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="row pt-3">
                            <div class="col-12"><label>Reviewer</label></div>

                            @foreach ($supervisors as $supervisor)
                                @php
                                    $edition = Edition::where('active',1)->first();
                                    $count = DB::table("posts")
                                    ->select("posts.*")->where('edition',$edition->id)
                                      ->whereRaw("find_in_set('".$supervisor->id."',posts.supervisors)")
                                        ->count();
                                @endphp
                                <div class="item col-md-6 col-xs-6 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="{{$supervisor->id}}"
                                               name="supervisors[]"
                                               value="{{$supervisor->id}}" {{ in_array($supervisor->id, $sup) ? 'checked' : ''}}>
                                        <label class="form-check-label"
                                               for="exampleCheck1">{{$supervisor->name}} {{$supervisor->surname}}
                                            ({{$count}})</label>
                                    </div>
                                </div>

                            @endforeach
                        </div>

                        <div class="row pt-3">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i> Save
                            </button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
                @endrole

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

