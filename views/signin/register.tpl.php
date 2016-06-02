<?php use ntentan\Session ?>
<h2>Register</h2>
<?php 

$helpers->form->setErrors($errors);
$helpers->form->setData($form_data);
echo $helpers->form->open('register-form');
echo $helpers->form->get_text_field('Firstname', 'firstname')->setRequired(true);
echo $helpers->form->get_text_field('Lastname', 'lastname');
echo $helpers->form->get_text_field('Othernames', 'othernames');
echo $helpers->form->get_text_field('Email Address', 'email')->setRequired(true);
?>
<hr/>
<?php
echo $helpers->form->get_text_field('Username', 'username')->setRequired(true);
echo $helpers->form->get_password_field('Password', 'password')->setRequired(true);
echo $helpers->form->get_password_field('Retype-Password', 'password2')->setRequired(true);
echo $helpers->form->close('Register');
?>