<h2>Signup</h2>
<?php if($_SESSION['third_party_authenticated'] !== true): ?>
<p>
    You may not need to register if you have an account with any of these 
    services. Just click on the appropriate service below to login with your
    already existing account.
</p>
<p>
    <a href="<?= u("{$social_signin_base_url}/signin/google") ?>" alt="Signin with Google">
        <img src="<?= u(load_asset('images/google.png', p('social/assets/images/google.png'))) ?>" />
    </a>
    <a href="<?= u("{$social_signin_base_url}/signin/yahoo") ?>" alt="Signin with Yahoo!">
        <img src="<?= u(load_asset('images/yahoo.png', p('social/assets/images/yahoo.png'))) ?>" />
    </a>
    <a  href="<?= u("{$social_signin_base_url}/signin/facebook") ?>" alt="Signin with facebook">
        <img src="<?= u(load_asset('images/facebook.png', p('social/assets/images/facebook.png'))) ?>" />
    </a>
</p>
<hr/>
<?php endif; ?>
<?php 

    $this->helpers->form->setErrors($errors);
    $this->helpers->form->setData($form_data);
    echo $this->helpers->form->open('register-form');
    echo $this->helpers->form->get_text_field('Firstname', 'firstname')->required(true);
    echo $this->helpers->form->get_text_field('Lastname', 'lastname');
    echo $this->helpers->form->get_text_field('Othernames', 'othernames');
    echo $this->helpers->form->get_text_field('Email', 'email')->required(true);
    echo $this->helpers->form->get_text_field('Username', 'username')->required(true);
    
    if(!$_SESSION['third_party_authenticated'])
    {
        echo $this->helpers->form->get_password_field('Password', 'password')->required(true);
        echo $this->helpers->form->get_password_field('Retype-Password', 'password2')->required(true);
    }
    echo $this->helpers->form->close('Register') 
?>
