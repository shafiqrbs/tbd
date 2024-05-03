@extends('nfccard::layouts.master')
@section('content')
    <div class="page-wrapper">
        <h1 class="d-none">NFC CARD Print</h1>
        <!-- Start of Header -->
    @include('nfccard::layouts.header')
    <!-- End of Header -->
        <!-- Start of Main-->
        <main class="main">
            <div class="page-content" style="min-height: 450px">
                <div class="container">
                    <form method="post" role="form" id="search-form">
                        <table id="leave_settings" class="shop-table account-orders-table mb-6 mt-8">
                            <thead class="thead-dark">
                            <tr>
                                <th class="order-id">S/N</th>
                                <th class="order-id">Company Name</th>
                                <th class="order-date">Name</th>
                                <th class="order-status">Designation</th>
                                <th class="order-total">Email</th>
                                <th class="order-actions">Phone</th>
                                <th class="order-actions">Action</th>
                            </tr>
                            </thead>

                            @if(sizeof($entities)>0)
                                <tbody>
                                @php $i=1; @endphp
                                @foreach($entities as $config)
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{$config->company}}</td>
                                        <td>{{$config->name}}</td>
                                        <td>{{$config->designation}}</td>
                                        <td>{{$config->email}}</td>
                                        <td>{{$config->phone}}</td>
                                        <td>
                                            <a href="{{--{{route('global_config_edit',[$config->id])}}--}}"
                                               class="btn btn-outline btn-default btn-block btn-sm btn-rounded"><i class="fas fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    @php $i++; @endphp
                                @endforeach
                                </tbody>
                            @endif
                        </table>
                        @if(isset($globalConfig) && count($globalConfig)>0)
                            <div class=" justify-content-right">
                                {{ $globalConfig->links('layouts.pagination') }}
                            </div>
                        @endif
                    </form>

                </div>
            </div>
        </main>
        <!-- End of Main -->
        <!-- Start of Footer -->
    @include('nfccard::layouts.footer')
    <!-- End of Footer -->
    </div>
    <!-- End of Page-wrapper-->
@endsection
