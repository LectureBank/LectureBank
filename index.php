<?php
	session_start();
	$title = "Welcome";
    include('header.php');
?>
  <h2>Welcome to LectureBank</h2>
  <p>We provide a networking tool for students, faculty, and researchers organizing lectures, seminars, conferences, or symposia to find qualified candidates to give original and interesting talks at their events. Our goal is to level the scientific playing field by creating more opportunities for postdoctoral researchers to bolster their credentials and improve their speaking skills.  In the process, we hope to promote general scientific literacy and wider interest in scientific careers.</p>
<h2>Find Some Talent</h2>
    <p>Search for local researchers whose experience and knowledge will enhance the event you are planning. Read brief abstracts summarizing the potential talks each candidate can give. Get in contact and invite them to speak live and in-person. Meet a great presenter or an interesting speaker at a conference or seminar? Find them by searching for the event you both attended and keep in touch!
</p>
    <h2>Get Out There</h2>
    <p>See the talks and events happening in your field and your geographic area. Hear about the latest and greatest topics in science, technology, engineering, and math from the researchers themselves. Meet other people with similar interests by attending lectures, and put yourself out there for the opportunity to be a guest speaker yourself. Get experience and gain confidence by actively seeking lecture opportunities that will strengthen your CV!</p>
    <div id="bigbutton">
     <a href="signup.php">Sign Up!</a>
    </div>
<br />
<br />
<br />
<?php
	include('footer.php');
?>