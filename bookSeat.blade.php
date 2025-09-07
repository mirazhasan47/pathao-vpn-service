@extends('blBusTicketWebView.header')
@section('pageTitle', 'Book Ticket')
@section('prev_page', url('/ticket/bus/bl/coach-details'))
@section('content')
    @php
        $provider = $request->cookie('provider') ?? '';
    @endphp
    <div class="container-fluid site-width">
        <div class="row mt-10">
            <div class="col-sm-12 col-12">
                <div class="destination-block">
                    <div class="row">
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Journey from</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ $request->cookie('from_station') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Journey to</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ $request->cookie('to_station') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Bus</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ $request->cookie('bus_name') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Boarding Point</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ $request->cookie('boarding_point_name') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Date</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ !empty($request->cookie('date')) ? date('d F Y', strtotime($request->cookie('date'))) : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Time</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ !empty($request->cookie('time')) ? date('h:i A', strtotime($request->cookie('time'))) : '--:--' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    @php
                        $seatLblArr = !empty($request->cookie('seat_lbls')) ? explode(',', $request->cookie('seat_lbls')) : [];
                        $totalSeat = !empty($seatLblArr) ? sizeof($seatLblArr) : '--';
                        $seats = !empty($seatLblArr) ? implode(' ', $seatLblArr) : '--';
                    @endphp
                    <div class="row">
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Total Seats</span>
                                <p class="font-size-14 bold font-dark-off-white">
                                    {{ $totalSeat }}
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-group">
                                <span class="font-size-14 font-dark-off-white">Seats</span>
                                <p class="font-size-14 bold font-bl">
                                    {{ $seats }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="" id="bookTicketForm" class="form-horizontal">
            <div class="row mt-30">
                <div class="col-sm-12 col-12">
                    <span class="font-size-20 font-dark-off-white bold">
                        Passenger Information
                    </span>
                </div>
                <div class="col-sm-12 col-12">
                    <div class="form-group">
                        <label class="font-size-14 font-dark-off-white" for="name">Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="passenger_name" class="form-control" id="name"
                            placeholder="Enter your full name">
                    </div>
                </div>
                <div class="col-sm-12 col-12">
                    <div class="form-group">
                        <label class="font-size-14 font-dark-off-white" for="email">Email <span
                                class="text-danger">*</span></label>
                        <input type="text" name="passenger_email" class="form-control" id="email"
                            placeholder="e.g. abc@xy.z">
                    </div>
                </div>
                <div class="col-sm-12 col-12">
                    <div class="form-group">
                        <label class="font-size-14 font-dark-off-white" for="mobile">Phone Number <span
                                class="text-danger">*</span></label>
                        <input type="number" name="passenger_mobile" class="form-control" id="mobile"
                            placeholder="e.g. 01xxxxxxxxx">
                    </div>
                </div>
                <div class="col-sm-6 col-6">
                    <div class="form-group">
                        <label class="font-size-14 font-dark-off-white" for="age">Age <span
                                class="text-danger">*</span></label>
                        <input type="number" name="passenger_age" class="form-control" id="age"
                            placeholder="e.g. 28">
                    </div>
                </div>
                <div class="col-sm-6 col-6">
                    <div class="form-group">
                        <label class="font-size-14 font-dark-off-white" for="gender">Gender <span
                                class="text-danger">*</span></label>
                        <select name="passenger_gender" id="gender" class="form-control form-select-2">
                            <option value="0">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>

                        </select>
                    </div>
                </div>
            </div>
        </form>
        <div class="row mt-10">
            <div class="col-sm-12 col-12">
                <button class="btn btn-sm btn-block green-bl book-ticket">
                    <span class="text-left">Book Ticket</span> <img src="{{ asset('assets/img/arrow-right.png') }}"
                        width="auto" height="30px">
                </button>
            </div>
        </div>

    </div>

@endsection
@push('scripts')
    <script type="text/javascript">
        $(function() {
            var options = {
                closeButton: true,
                debug: false,
                positionClass: "toast-bottom-right",
                onclick: null,
            };

            var focusInput = '#passenger_name,#passenger_email,#passenger_mobile,#passenger_age,#gender';
            $(document).on('click', focusInput, function(e) {
                var myEl = $(this);
                $('body,html').stop(false, false).animate({
                    scrollTop: myEl.offset().top
                }, 2000, 'linear', function() {
                    myEl.focus();
                    alert('test');
                });
            });

            $(document).on('click', '.book-ticket', function(e) {
                e.preventDefault();
                // Serialize the form data
                var formData = new FormData($('#bookTicketForm')[0]);

                $.ajax({
                    url: "{{ url('/ticket/bus/bl/book-ticket') }}",
                    type: "POST",
                    dataType: 'json', // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('.book-ticket').prop('disabled', true);
                        blockUI(1);
                    },
                    success: function(res) {
                        $('.book-ticket').prop('disabled', false);
                        var url = res.url;
                        location = 'confirm-ticket' + url;
                    },
                    error: function(jqXhr) {
                        if (jqXhr.status == 400) {
                            var errorsHtml = '';
                            var errors = jqXhr.responseJSON.message;
                            $.each(errors, function(key, value) {
                                errorsHtml += '<li>' + value + '</li>';
                            });
                            toastr.error(errorsHtml, jqXhr.responseJSON.heading,
                                options);
                        } else if (jqXhr.status == 401) {
                            toastr.error(jqXhr.responseJSON.message, '',
                                options);
                        } else {
                            toastr.error('Error', 'Something went wrong',
                                options);
                        }
                        $('.book-ticket').prop('disabled', false);
                        blockUI(0);
                    }
                });
            });
        });
    </script>
@endpush
