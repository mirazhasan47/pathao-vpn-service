<!DOCTYPE html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>PayPlus</title>
  <link rel="icon" type="image/png" href="{{asset('assets//img/payplusbn.png')}}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/bootstrap/css/bootstrap.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('assets/login/util.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('assets/login/main.css')}}">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
</head>
<body>
 <div class="limiter">
  <div class="container-login100">
    <div class="wrap-login100">          
     <form id="login_form">
      {{ csrf_field() }}
      <span class="login100-form-title p-b-26">
        Welcome
      </span>
      <span class="login100-form-title p-b-48">
        <img src="{{asset('assets//img/payplusbn.png')}}" style="width: 200px; height: auto;">
      </span>
      <div class="wrap-input100 validate-input" >
        <input class="input100" type="text"  name="email" id="email" >
        <span class="focus-input100" data-placeholder="Username"></span> 
      </div>

      <div class="wrap-input100 validate-input" data-validate="Enter password">
        <span class="btn-show-pass">
          <i class="fa fa-eye"></i>
        </span>
        <input class="input100" type="password" name="password" id="password" autocomplete="off">
        <span class="focus-input100" data-placeholder="Password"></span>
      </div>
      <div class="container-login100-form-btn">
        <div class="wrap-login100-form-btn">
          <div class="login100-form-bgbtn"></div>

          <button class="login100-form-btn" type="submit">
            Login
          </button>
          
        </div>
      </div>
    </form>
  </div>
</div>
</div>
<script src="{{ asset('assets/vendors/jquery/jquery-3.3.1.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript">
  function showMessage(type, message) {
    if(type == 'success'){
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message
      });
    }else if(type == 'error'){
     Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message
    });
   }else if(type == 'warning'){
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message
    });
  }else if(type == 'fail'){
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message
    });
  }
}

$("#login_form").on('submit', function (event) {
  event.preventDefault();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

  var email = $("#email").val();
  var password = $("#password").val();
  $.ajax({
    type: "POST",
    url: "{{ url('/loginCheck') }}",
    data: {
      'email': email,
      'password': password,
      '_token': '{{csrf_token()}}'
    },
    success: function (data) {
      if (data.result == "fail") {
        showMessage('error','Invalid email or password');
      }else if (data.result == "empty") {
        showMessage('error','Email or password can not be empty');
      } else if (data.result == "success") {
        if(data.user_type==2){
          window.location = '{{ url("/dashboard") }}';
        }else{
          if(data.role==4 || data.role==5){
            window.location = '{{ url("/dashboard-lite") }}';
          }else{
            window.location = '{{ url("/dashboard-lite") }}';
          }          
        }

      }
    }
  });
});

</script>
</body>
</html>