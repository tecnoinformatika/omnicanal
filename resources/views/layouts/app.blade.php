@include('layouts.partials.header') <!-- Include the header partial -->

<div class="page-wrapper">
    <!-- Page Content-->
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            @yield('page-title') <!-- Define this section in your child views -->

            <!-- Page content -->
            @yield('content') <!-- Define this section in your child views -->
        </div><!-- container -->
    </div><!-- end page-content-tab -->
</div><!-- end page-wrapper -->

@include('layouts.partials.footer') <!-- Include the footer partial -->
