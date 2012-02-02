<?php
	session_start();
	$title = "About Us";
    include('header.php');
?>
<header>
<nav id="abouttop" class="aboutfaq">
<ul>
<li><a href="#about">About</a></li>
<li><a href="#faq">FAQs</a></li>
</ul>
</nav>
</header>
<section id="about">
<header>
<h2>About Us</h2>
</header>
  <p>LectureBank started in August, 2010 as an Interdisciplinary Qualifying Project undertaken by Giuseppe Ciliotta, Gregory Dracoulis, and Shaoming Feng at WPI (<a href="http://www.wpi.edu">Worcester Polytechnic Institute</a>) under the advisement of Dominic Golding and Luis Vidali. The overall aim of the project at its inception was to improve equity and retention across the spectrum of STEM (Science, Technology, Engineering, and Math) fields. Starting with such broad aspirations, the team had to explore the full scope of the problem and different avenues by which they could effect a change.</p>

<p>Eventually, they seized on the idea of creating a searchable network of postdoctoral researchers in order to better connect that community and create more opportunities for this broadly-spread population to interact, not just online, but in real life too. Seeing that researchers could use talks and lectures as valuable tools to increase their exposure to the wider scientific community, and that conference and seminar organizers often were at a loss with regard to ways in which possible speakers might be found, the team embarked on the path to putting the two together through a network-based searched that would decrease reliance on preexisting personal connections and instead allow for new ones to be forged, increasing fairness and opportunity at the upper levels of STEM.</p>

<p>By helping scientists, engineers, and mathematicians identify new avenues for professional growth and giving event planners access to a wider and more diverse pool of talent and brainpower, the team hoped they might start a positive trend to improve morale and retention across entire disciplines. They hoped that by starting near the top, the positive effects and changing landscape might be seen by upcoming and aspiring professionals and trickle down.</p>

<p>In May of 2011, the team finally presented their plan and recommendations, and eventually they received a grant from the <a href="http://www.nsf.gov">National Science Foundation</a> that allowed one of them to stay on during the summer and implement the ideas they worked so hard to generate. This effort has culminated in the site you see here today. We plan on growing and improving the site, constantly moving forward in order to make progress in achieving the broad goals initially set at the outset of this endeavor. We're glad you can be part of it.</p>
</section>
<section id="faq">
<header>
<h2>Frequently Asked Questions</h2>
<nav class="aboutfaq">
<ul>
<li><a href="#faq1">How is this different from posting my CV on my lab’s website?</a></li>
<li><a href="#faq2">How is my privacy protected?</a></li>
<li><a href="#faq3">Where is this site in use? What are your plans to expand?</a></li>
</ul>
</nav>
</header>
<section id="faq1">
<header>
<h3>How is this different from posting my CV on my lab’s website?</h3>
</header>
<p>We provide a centralized, searchable repository (or "lecture bank") that not only allows you to be found much more quickly and in context, but also enables you to be presented with relevant opportunities tailored to your location, qualifications, and research interests. Your CV most likely exists in a confusing, deep hierarchy of pages that is difficult for search engines to crawl, and even more difficult for another human-even one with similar interests-to find, except perhaps by name. By adding yourself to this specialized network, you will be increasing your visibility and the ease with which someone who is part of your local professional community might be able to find you by orders of magnitude. </p>
</section>
<section id="faq2">
<header>
<h3>How is my privacy protected?</h3>
</header>
<p>LectureBank engages in a number of practices specifically geared to protect your privacy. Your email address, though kept human-readable, is obfuscated (hidden) to any automated program unless searched directly, so spammers or other nefarious parties cannot traverse our site and harvest them easily. Your information is encrypted in a password-protected database that is only accessible by authorized parties sitting at specifically authorized computers. We also employ sophisticated systems to detect suspicious activity on our site, and agressively lock those parties out unless they can prove they are human by completing a CAPTCHA. We continue to work on implementing finer grained privacy controls to allow you to control exactly who sees which elements of your profile, and they will be available soon.</p>
</section>
<section id="faq3">
<header>
<h3>Where is this site in use? What are your plans to expand?</h3>
</header>
<p>We have deployed LectureBank across parts of the Biology and Biotechnology departments at <a href="http://www.wpi.edu">WPI</a>, and are working towards having the system adopted by the entire department by early January, and university-wide for both lecture invitations and consideration for faculty positions by the end of this academic year. We also plan to rapidly roll out to departments at <a href="http://www.harvard.edu">Harvard</a> and <a href="http://www.mit.edu">MIT</a> in the near-term, and we intend to collaborate with other institutions in the New England area (including <a href="http://www.bu.edu">Boston University</a>, <a href="http://www.clarku.edu">Clark University</a>, <a href="http://www.northeastern.edu">Northeastern</a>, <a href="http://www.tufts.edu">Tufts</a>, <a href="http://www.umass.edu">UMass</a>, and <a href="http://www.uconn.edu">UConn</a>) to every extent possible.</p>
</section>
</section>
<?php
	include('footer.php');
?>