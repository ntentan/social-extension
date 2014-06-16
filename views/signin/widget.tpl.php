<div class="login-form">
    <?php if($login_error): ?>
    <div class="form-errors">
        <ul><li>Invalid username or password</li></ul>
    </div>
    <?php endif; ?>
    <?php
    echo $helpers->form->open('login-form')->action(u($social_signin_base_url . '/signin'));
    echo $helpers->form->get_text_field('Username or Email', 'username');
    echo $helpers->form->get_password_field('Password', 'password');
    ?>
    <?php echo $this->helpers->form->close('Login'); ?>
    <!-- <a href="<?= u($social_signin_base_url . '/forgotten_password') ?>">Forgotten your password?</a><br/>-->
    <a href="<?= u($social_signin_base_url . '/register') ?>">Register for a new account</a>
</div>

<div id="third-party-accounts">
    <p>You can also use your third party accounts from any of these services
    <br/>
    <a href="<?= u($social_signin_base_url . '/signin/google') ?>" alt="Signin with Google"><img src="<?= u(load_asset('images/google.png', __DIR__ . ('/../../assets/images/google.png'))) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/yahoo') ?>" alt="Signin with Yahoo!"><img src="<?= u(load_asset('images/yahoo.png',  __DIR__ . ('/../../assets/images/yahoo.png'))) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/facebook') ?>" alt="Signin with facebook"><img src="<?= u(load_asset('images/facebook.png',  __DIR__ . ('/../../assets/images/facebook.png'))) ?>" /></a>
    </p>
</div>
