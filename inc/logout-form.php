<?php if (isset($_SESSION['loggedin'])) { ?>
  <form action="<?php echo PATH_INDEX; ?>" method="post">
    <input type="hidden" name="logout" value="1" />
    <br />
    <button class="button tiny warning" type="submit">
      Log Out <i class="fa fa-sign-out" aria-hidden="true"></i>
    </button>
  </form>
<?php } ?>
