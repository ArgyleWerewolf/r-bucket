<form action="<?php echo PATH_INDEX; ?>" method="post">
  <input type="hidden" name="login" value="1" />

  <label for="username">Username</label>
  <input id="username" name="username" type="text" value="<?php echo (isset($_POST["username"])) ? $_POST["username"] : ''; ?>" maxlength="255" />

  <label for="password">Password</label>
  <input id="password" name="password" type="password" autocomplete="off" value="<?php echo (isset($_POST["password"])) ? $_POST["password"] : ''; ?>" maxlength="255" />

  <button class="button" type="submit">Sign In</button>
</form>
