<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HJ | Hexa Skeleton</title>
    <link rel="icon" href="~/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('css/plugins/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}" id="main-font-link" />
    <link rel="stylesheet" href="{{ asset('fonts/poppins/poppins.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('css/style-preset.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<!-- [Head] end -->

<!-- [Body] Start -->

<body>
    <!-- [ Pre-loader ] start -->
    @include('partials._loader')
    <!-- [ Pre-loader ] End -->

    <div class="auth-main">
        <div class="auth-wrapper v1">
            <div class="auth-form">
                <div class="card my-3" style="background-color: #FDFDFD">
                    <div class="card-body">
                        <div class="text-center">
                            <a href="/Auth/Login">
                                <img src="{{ asset('/images/authentication/logo.png') }}" class="w-75" alt="img" referrerpolicy="no-referrer" />
                            </a>
                            <br />
                            <br />
                            <h6 class="title-app">Hexa Skeleton Digitalization</h6>
                            <p>Welcome back</p>
                        </div>
                        <div class="saprator my-3"></div>
                        <form class="form-login">
                            <div class="form-group mb-3">
                                <input type="email" class="form-control" id="email" placeholder="Email Address" />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="input-group has-validation mb-3">
                                    <input type="password" class="form-control"
                                        name="password" autocomplete="current-password" placeholder="Password" id="password">
                                    <span class="input-group-text" id="togglePassword"><i class="fa fa-eye"></i></span>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="button" class="btn btn-primary" id="login-btn">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- [ Main Content ] end -->

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/sweetalert.js') }}"></script>

    <script>
        $(document).ready(function() {
            // ✅ Include CSRF token in all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function handleLogin() {
                $('#login-btn').prop('disabled', true);
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $('#error-msg').html('');
                $('#error-resp').hide();

                var email = $('#email').val();
                var password = $('#password').val();

                $.ajax({
                    url: "{{ route('doLogin') }}",
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        'email': email,
                        'password': password
                    }),
                    success: function(resp) {
                        $('#login-btn').prop('disabled', false);
                        Swal.fire({
                            title: 'Good',
                            text: resp.message,
                            icon: 'success',
                        }).then(() => {
                            window.location.href = resp.redirectUrl;
                        })
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 400) {
                            const errors = JSON.parse(xhr.responseText);
                            for (let key in errors) {
                                const errorMessage = errors[key].join('<br>');
                                $('#' + key.toLowerCase()).addClass('is-invalid').siblings('.invalid-feedback').html(errorMessage);
                            }
                        } else if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                const errorMessage = errors[key].join('<br>');
                                $('#' + key.toLowerCase()).addClass('is-invalid').siblings('.invalid-feedback').html(errorMessage);
                            }
                        } else if (xhr.status === 504) {
                            $('#email').addClass('is-invalid').siblings('.invalid-feedback').html('504: Gateway timeout');
                        } else if (xhr.status === 401) {
                            $('#email').addClass('is-invalid').siblings('.invalid-feedback').html('Username or password is incorrect');
                        } else {
                            $('#email').addClass('is-invalid').siblings('.invalid-feedback').html('something went wrong');
                        }
                        $('#login-btn').prop('disabled', false);
                        console.log(xhr.status, xhr.responseText);
                    }
                });
            }

            // Trigger login on Enter key press
            $('#email, #password').keypress(function(event) {
                if (event.which === 13) { // 13 is the Enter key code
                    handleLogin();
                }
            });

            // Trigger login on button click
            $('#login-btn').on('click', function(event) {
                event.preventDefault(); // Prevent form submission
                handleLogin();
            });

            // Toggle password
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const icon = $(this).find('i');

                const isPasswordVisible = passwordInput.attr('type') === 'text';
                passwordInput.attr('type', isPasswordVisible ? 'password' : 'text');

                icon.toggleClass('fa-eye fa-eye-slash');
            });
        })
    </script>
</body>
<!-- [Body] end -->

</html>