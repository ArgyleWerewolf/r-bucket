<?php if (isset($_SESSION['loggedin'])) { ?>
  <form action="<?php echo UPLOAD_ACTION; ?>" method="post">
    <input type="hidden" name="logout" value="1" />
    <br />
    <input class="button tiny warning" value="Log Out" type="submit">
  </form>
<?php } ?>
