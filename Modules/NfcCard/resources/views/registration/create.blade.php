@extends('nfccard::layouts.master')
@section('content')
    <div class="page-wrapper">
        <h1 class="d-none">NFC CARD Print</h1>
        <!-- Start of Header -->
    @include('nfccard::layouts.header')
    <!-- End of Header -->
    <!-- Start of Main-->
    <main class="main">
        <div class="page-content contact-us">
            <div class="container">
                <section class="contact-section">
                    <div class="row gutter-lg pb-3">
                        <div class="col-lg-12 mb-8">
                            <br/>
                            <br/>
                            <br/>
                            <br/>
                            <h4 class="title mb-3">Send Us a Message</h4>
                            @if(Session::has('success'))
                                <div class="alert alert-success text-center">
                                    {{Session::get('success')}}
                                </div>
                            @endif
                            <form  method="post" action="{{ route('validate.form') }}"  novalidate enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-2">
                                    <label>Company Name</label>
                                    <input type="text" value="{{old('name')}}" class="form-control @error('name') is-invalid @enderror" name="name" id="name">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                 <div class="form-group mb-2">
                                    <label>Designation</label>
                                    <input type="text" class="form-control @error('designation') is-invalid @enderror" name="designation" id="designation">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone">
                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Website</label>
                                    <input type="text" class="form-control @error('website') is-invalid @enderror" name="website" id="website">
                                    @error('subject')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Profile Pic</label>
                                    <input type="file" class="form-control @error('profile_pic') is-invalid @enderror" name="profile_pic" id="profile_pic">
                                    @error('subject')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Company Logo</label>
                                    <input type="file" class="form-control @error('company_logo') is-invalid @enderror" name="company_logo" id="company_logo">
                                    @error('subject')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label>Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="4"></textarea>
                                    @error('address')
                                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                                    @enderror
                                </div>

                                <div class="d-grid mt-3">
                                    <input type="submit" name="send" value="Submit" class="btn btn-dark btn-block">
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <!-- End of Main -->
    <!-- Start of Footer -->
    @include('nfccard::layouts.footer')
    <!-- End of Footer -->
    </div>
    <!-- End of Page-wrapper-->
    <style>

        .form-control:focus {
            border-color: #000;
            box-shadow: none;
        }
        label {
            font-weight: 600;
        }
        .invalid-feedback strong{
            color: red;
            font-weight: 400;
            display: block;
            padding: 6px 0;
            font-size: 14px;
        }
        .error {
            color: red;
            font-weight: 400;
            display: block;
            padding: 6px 0;
            font-size: 14px;
        }
        .form-control.error {
            border-color: red;
            padding: .375rem .75rem;
        }
    </style>
@endsection
