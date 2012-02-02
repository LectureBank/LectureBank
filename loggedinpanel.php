<div id="loggedInPanel"><strong><?php echo $_SESSION['email']; ?></strong><br />
is logged in<br /><br />
<nav id="panel">
<ul>
	<li><a href="/profile.php">Edit Profile</a></li>
    <li><a href="/<?php echo $_SESSION['username']; ?>">Preview Profile</a></li>
	<li><a href="/logout.php">Logout</a></li>
	</ul>
</nav>
</div>