@extends('layouts.app')

@section('page-title')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="float-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Metrica</a>
                    </li><!--end nav-item-->
                    <li class="breadcrumb-item"><a href="#">Ecommerce</a>
                    </li><!--end nav-item-->
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
            <h4 class="page-title">Dashboard</h4>
        </div><!--end page-title-box-->
    </div><!--end col-->
</div>
@endsection
@section('content')
    <!-- end page title end breadcrumb -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Revenu Status</h4>
                        </div><!--end col-->
                        <div class="col-auto">
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                This Month<i class="las la-angle-down ms-1"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Today</a>
                                    <a class="dropdown-item" href="#">Last Week</a>
                                    <a class="dropdown-item" href="#">Last Month</a>
                                    <a class="dropdown-item" href="#">This Year</a>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="">
                        <div id="Revenu_Status" class="apex-charts"></div>
                    </div>
                </div><!--end card-body-->
            </div><!--end card-->
        </div><!-- end col-->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col align-self-center">
                            <div class="media">
                                <img src="assets/images/logos/money-beg.png" alt="" class="align-self-center" height="40">
                                <div class="media-body align-self-center ms-3">
                                    <h6 class="m-0 font-24">$1850.00</h6>
                                    <p class="text-muted mb-0">Total Revenue</p>
                                </div><!--end media body-->
                            </div><!--end media-->
                        </div><!--end col-->
                        <div class="col-auto align-self-center">
                            <div class="">
                                <div id="Revenu_Status_bar" class="apex-charts mb-n4"></div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end card-->
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col text-center">
                                    <span class="h5">$24,500</span>
                                    <h6 class="text-uppercase text-muted mt-2 m-0 font-11">Today's Revenue</h6>
                                </div><!--end col-->
                            </div> <!-- end row -->
                        </div><!--end card-body-->
                    </div> <!--end card-body-->
                </div><!--end col-->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col text-center">
                                    <span class="h5">520</span>
                                    <h6 class="text-uppercase text-muted mt-2 m-0 font-11">Today's New Order</h6>
                                </div><!--end col-->
                            </div> <!-- end row -->
                        </div><!--end card-body-->
                    </div> <!--end card-body-->
                </div><!--end col-->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col text-center">
                                    <span class="h5">82.8%</span>
                                    <h6 class="text-uppercase text-muted mt-2 m-0 font-11">Conversion Rate</h6>
                                </div><!--end col-->
                            </div> <!-- end row -->
                        </div><!--end card-body-->
                    </div> <!--end card-body-->
                </div><!--end col-->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col text-center">
                                    <span class="h5">$80.5</span>
                                    <h6 class="text-uppercase text-muted mt-2 m-0 font-11">Avg. Value</h6>
                                </div><!--end col-->
                            </div> <!-- end row -->
                        </div><!--end card-body-->
                    </div> <!--end card-->
                </div><!--end col-->
            </div><!--end row-->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">View Invoices</h4>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="las la-file-invoice-dollar font-36 text-muted"></i>
                        </div><!--end col-->
                        <div class="col">
                            <div class="input-group">
                                <select class="form-select">
                                    <option selected>--- Select ---</option>
                                    <option value="Jan 2021">Jan 2021</option>
                                    <option value="Feb 2021">Feb 2021</option>
                                    <option value="Mar 2021">Mar 2021</option>
                                    <option value="Apr 2021">Apr 2021</option>
                                </select>
                                <button class="btn btn-soft-primary btn-sm" type="button"><i class="las la-search"></i></button>
                            </div>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-body-->
            </div><!--end card-->
        </div><!-- end col-->
    </div><!--end row-->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Earnings Reports</h4>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0">Date</th>
                                    <th class="border-top-0">Item Count</th>
                                    <th class="border-top-0">Text</th>
                                    <th class="border-top-0">Earnings</th>
                                </tr><!--end tr-->
                            </thead>
                            <tbody>

                            </tbody>
                        </table> <!--end table-->
                    </div><!--end /div-->
                </div><!--end card-body-->
            </div><!--end card-->
        </div> <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Most Populer Products</h4>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0">Product</th>
                                    <th class="border-top-0">Price</th>
                                    <th class="border-top-0">Sell</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0">Action</th>
                                </tr><!--end tr-->
                            </thead>
                            <tbody>

                            </tbody>
                        </table> <!--end table-->
                    </div><!--end /div-->
                </div><!--end card-body-->
            </div><!--end card-->
        </div> <!--end col-->
    </div><!--end row-->
@endsection
