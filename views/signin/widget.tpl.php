<div class="login-form">
    <?php if($login_error): ?>
    <div class="form-errors">
        <ul><li>Invalid username or password</li></ul>
    </div>
    <?php endif; ?>
    <?php
    $helpers->form->setData($_POST);
    echo $helpers->form->open('login-form');
    echo $helpers->form->get_text_field('Username or Email', 'username');
    echo $helpers->form->get_password_field('Password', 'password');
    ?>
    <a href="<?= u($social_signin_base_url . '/forgotten_password') ?>">Forgotten your password?</a><br/>
    <a href="<?= u($social_signin_base_url . '/signup') ?>">Register for a new account</a>
    <?php echo $this->helpers->form->close('Login'); ?>
</div>

<div id="third-party-accounts">
    <p>You can also use your third party accounts from any of these services</p>
    <br/>
    <a href="<?= u($social_signin_base_url . '/signin/google') ?>" alt="Signin with Google"><img src="<?= load_asset('images/google.png', p('social/assets/images/google.png')) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/yahoo') ?>" alt="Signin with Yahoo!"><img src="<?= load_asset('images/yahoo.png', p('social/assets/images/yahoo.png')) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/facebook') ?>" alt="Signin with facebook"><img src="<?= load_asset('images/facebook.png', p('social/assets/images/facebook.png')) ?>" /></a>
    
</div>
