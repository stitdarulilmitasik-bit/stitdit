@extends('core-themes.core-mainpage')
@section('content')
    <div class="page page-center">
        <div class="container container-normal py-4">
            <div class="row align-items-center g-4">
                <div class="col-lg">
                    <div class="container-tight">
                        <div class="text-center mb-4">
                            <!-- BEGIN NAVBAR LOGO -->
                            <a href="." aria-label="Tabler" class="navbar-brand navbar-brand-autodark">
                                <img src="{{ stit_storage_image_url('images/logo', 'logo-hori.png', 'images/logo/logo-hori.png') }}" style="height: 64px; width:auto; max-width:260px; object-fit:contain;" alt="Logo STIT Darul Ilmi">
                            </a>
                            <!-- END NAVBAR LOGO -->
                        </div>
                        <div class="card card-md">
                            <div class="card-body">
                                <h2 class="h2 text-center mb-4">Login to your account</h2>
                                <form action="{{ route('auth.handle-signin') }}" method="post" autocomplete="on" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Email address</label>
                                        <input type="text" class="form-control" name="login" placeholder="Email or Username" autocomplete="on" />
                                        @error('login')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">
                                            Password
                                            <span class="form-label-description">
                                                <a href="./forgot-password.html">I forgot password</a>
                                            </span>
                                        </label>
                                        <div class="input-group input-group-flat">
                                            <input type="password" class="form-control" name="password" placeholder="Your password" autocomplete="off" />
                                            <span class="input-group-text">
                                                <a href="javascript:void(0)" class="link-secondary toggle-password" title="Show password" data-bs-toggle="tooltip"><!-- Download SVG icon from http://tabler.io/icons/icon/eye -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                                    </svg>
                                                </a>
                                            </span>
                                            <br>
                                        </div>
                                        @error('password')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-check">
                                            <input type="checkbox" class="form-check-input" />
                                            <span class="form-check-label">Remember me on this device</span>
                                        </label>
                                    </div>
                                    @if($webs->enable_captcha == true || $webs->enable_captcha == 1 || $webs->enable_captcha == "1")
                                    <div class="">
                                        <x-turnstile-widget theme="auto" language="id"/>
                                        @error('cf-turnstile-response')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    @endif
                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
                                    </div>

                                </form>
                            </div>
                            <div class="hr-text">or</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <a href="#" class="btn btn-4 w-100">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/brand-github -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-github icon-2">
                                                <path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" />
                                            </svg>
                                            Login with Github
                                        </a>
                                    </div>
                                    <div class="col">
                                        <a href="#" class="btn btn-4 w-100">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/brand-x -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-x icon-2">
                                                <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                                                <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                                            </svg>
                                            Login with X
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-secondary mt-3">Don't have account yet? <a href="./sign-up.html" tabindex="-1">Sign up</a></div>
                    </div>
                </div>
                <div class="col-lg d-none d-lg-flex align-items-center justify-content-center">
                    <div class="text-center w-100">
                        <img
                            src="{{ stit_storage_image_url('images/logo', 'logo-vert.png', 'images/logo/logo-vert.png') }}"
                            class="img-fluid d-block mx-auto"
                            style="max-width: 360px; max-height: 460px; width: auto; height: auto; object-fit: contain;"
                            alt="Logo STIT Darul Ilmi"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('custom-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('input[type="password"]');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the eye icon
                const icon = this.querySelector('svg');
                if (type === 'password') {
                    icon.innerHTML = `
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                    `;
                } else {
                    icon.innerHTML = `
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                        <path d="M3 3l18 18" />
                    `;
                }
            });
        }
    });
</script>
@endsection
