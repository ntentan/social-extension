<?php use ntentan\Session ?>
<h2>Signup</h2>
<?php if(Session::get('third_party_authenticated') === true): ?>
<p>
    Here's the profile we imported from <b><?= $_SESSION['third_party_provider'] ?></b>.
    Hope you're well represented.
</p>
<?php else: ?>
<p>
    You may not need to register if you have an account with any of these 
    services. Just click on the appropriate service below to login with an
    existing account.
</p>
<p>
    <a href="<?= u($social_signin_base_url . '/signin/google') ?>" alt="Signin with Google"><img src="<?= u(load_asset('images/google.png', __DIR__ . ('/../../assets/images/google.png'))) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/yahoo') ?>" alt="Signin with Yahoo!"><img src="<?= u(load_asset('images/yahoo.png',  __DIR__ . ('/../../assets/images/yahoo.png'))) ?>" /></a>
    <a href="<?= u($social_signin_base_url . '/signin/facebook') ?>" alt="Signin with facebook"><img src="<?= u(load_asset('images/facebook.png',  __DIR__ . ('/../../assets/images/facebook.png'))) ?>" /></a>
</p>
<hr/>
<?php endif; ?>
<?php 

    $helpers->form->setErrors($errors);
    $helpers->form->setData($form_data);
    echo $helpers->form->open('register-form');
    echo $helpers->form->get_text_field('Firstname', 'firstname')->setRequired(true);
    echo $helpers->form->get_text_field('Lastname', 'lastname');
    echo $helpers->form->get_text_field('Othernames', 'othernames');
    echo $helpers->form->get_text_field('Email', 'email')->setRequired(true);
    echo $helpers->form->get_text_field('Username', 'username')->setRequired(true);
    
    if(Session::get('third_party_authenticated'))
    {
        echo $helpers->form->get_password_field('Password', 'password')->setRequired(true);
        echo $helpers->form->get_password_field('Retype-Password', 'password2')->setRequired(true);
        echo $helpers->form->close('Register');
    }
    else
    {
        echo $helpers->form->close("Save " . Session::get('third_party_provider') . " profile");
    } 
