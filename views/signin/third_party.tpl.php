<?php if($status == 'cancelled'): ?>
<h2>Couldn't sign in through <?= $provider ?></h2>
<p>
    It appears you canceled your third party sign in through <?= $provider ?>.
    If you have concerns with respect to privacy you can review our privacy policy.
    All the same you can register for an account directly on our servers <a href="/register">here</a>.
</p>
<?php endif; ?>


<?php if($status == 'failed'): ?>
<h2>Couldn't sign in through <?= $provider ?></h2>
<p>
    Your third party sign in through <?= $provider ?> failed.
    All the same you can register for an account directly on our servers <a href="/register">here</a>.
</p>
<?php endif; ?>

<?php if($status == 'existing'):?>
<h2>Hmm! Seems like you are already here</h2>
<p>
    There seems to be an account on <?= $app ?> which has some characteristics similar to
    the <?= $provider ?> profile you are logging in with. If you already have an <?= $app ?>
    account and you want to sign in with this <?= $provider ?> profile,
    you might want to sign in with your afrojamz profile first.
</p>
</php>
<?php endif ?>

<?php if($status == 'no_profile'): ?>
<h2>Your <?= $provider ?> sign in was successful</h2>
<p>Thanks <b><?= $name ?></b>, you have successfully signed in through <b><?= $provider?></b>. We 
however cannot find an <b><?= $app ?></b> profile which matches your <b><?= $provider ?></b> profile. 
This is however not a problem. You can either link to an existing <?= $app ?> profile (for which you must have a username and password) or 
you can create a new <?= $app ?> profile. We will even give you the option to import 
your profile details from <b><?= $provider ?></b>. How cool is that? Anyway, how would you
want us to treat this situation?
</p>

<p onclick="" >
    <a href="#" onclick="$('#link-form').slideToggle()">I want to link this profile to an existing <?= $app ?> Profile</a>
    <div class="row" id="link-form" style="display:none">
        <div class="column grid_10_5">
        <?php
        echo $this->helpers->forms->open('login-form')->action("/users/link_profiles");
        echo $this->helpers->forms->get_text_field("Username or Email", "username");
        echo $this->helpers->forms->get_password_field("Password", "password");
        echo $this->helpers->forms->close('Link Accounts');
        ?>
        </div>
    </div>
    <div class="row">
    </div>
</p>

<p>
    <a href="#" onclick="$('#new-profile').slideToggle()">I want to create a new <?= $app ?> profile</a>
    <div id="new-profile" style="display:none">
        <?php
        echo $this->helpers->forms->open('profile-import-form')->action("/users/$register");
        echo $this->helpers->forms->get_radio_button("Import my profile data from $provider", "action", "import")->attribute('checked', 'checked');
        echo $this->helpers->forms->get_radio_button("I'll provide new profile data", "action", "create");
        echo $this->helpers->forms->close('Create Profile');
        ?>
    </div>
</p>

<?php endif; ?>
