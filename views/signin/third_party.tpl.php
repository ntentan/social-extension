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
    account sign in with it.
</p>
</php>
<?php endif ?>
