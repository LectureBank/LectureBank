<?php
	require_once('config/database-connect.php');
	require_once('includes/wordhelper.php');
	
	$username = clean($_GET["user"]);
	$papertok = clean($_GET["research"]);
	
	$papersqry = "SELECT research.id AS id, research.title AS title FROM research, users WHERE users.username = '$username' && research.uid = users.id_user";
	$papersresult = mysql_query($papersqry);
	while($papercheck = mysql_fetch_array($papersresult)) {
		if (strcmp(cleanSlug($papercheck['title']),$papertok) == 0) {
			$paperid = $papercheck['id'];
			break;
		}
	}
	@mysql_free_result($papersresult);
	
	$title = "Research Details";
	if($paperid) {
		$paperqry = "SELECT research.title AS title, research.link AS link, research.yr AS year, research.abstract AS abst, research.journal AS journal, research.volume AS volume, research.issue AS issue, research.startpg AS startpg, research.endpg AS endpg, research.doi AS doi, users.name AS author, users.username AS username, institutions.name AS authorinst FROM research, users, userinstitutions, institutions WHERE research.id=$paperid && users.id_user=research.uid && userinstitutions.uid=users.id_user && institutions.id=userinstitutions.instid";
		$paperresult = mysql_query($paperqry);
		$paper = mysql_fetch_array($paperresult); 
		
		@mysql_free_result($paperresult);
		
		$papertagqry = "SELECT tags.id, tags.tag FROM tags, researchtags WHERE researchtags.research = '$paperid' && tags.id = researchtags.tag";
		$papertagresult = mysql_query($papertagqry);
		while($papertag = mysql_fetch_array($papertagresult)) {
			$papertags[] = $papertag['tag'];
		}
		@mysql_free_result($papertagresult);
		
		$title = $paper['title'].' '.$title;
		$author = $paper['author'];
		if(!empty($papertags)){
			$metakeywords = implode(",", $papertags);
		}
		if(!empty($paper['abst'])){
			$metadescription = $paper['abst'];
		}
		include('header.php');
		
		echo ('<article itemscope itemtype="http://schema.org/ScholarlyArticle">');
		echo ("<header>");
		echo ("<hgroup>");
		echo ('<h1 itemprop="name">'.$paper['title'].'</h1>');
		echo('<h3><span itemprop="author" itemscope itemtype="http://schema.org/Person"><a href="/'.$paper['username'].'" itemprop="url" rel="author"><span itemprop="name">'.$paper['author'].'</span></a>, <span itemprop="affiliation" itemscope itemtype="http://schema.org/Organization"><span itemprop="name">'.$paper['authorinst'].'</span></span></span> (<span itemprop="copyrightYear">'.$paper['year'].'</span>)</h2>');
		if(!empty($paper['journal'])){
			echo('<h3><span itemprop="publisher" itemscope itemtype="http://schema.org/Organization"><span itemprop="name">'.$paper['journal'].'</span></span>');
			if(!empty($paper['volume'])) echo(', vol. '.$paper['volume']);
			if(!empty($paper['issue'])) echo(', iss. '.$paper['issue']);
			if(!empty($paper['startpg'])) echo(', '.$paper['startpg']);
			if(!empty($paper['endpg'])) echo('-'.$paper['endpg']);
			if(!empty($paper['doi'])) echo(' DOI: <a itemprop="url" href="http://dx.doi.org/'.$paper['doi'].'" target="_blank" rel="external">'.$paper['doi'].' <img alt="Resolve DOI" src="/images/external-link-icon.gif"></a>');
			echo('</h3>');
		}
		echo('</hgroup>');
		echo('</header>');
		
		echo ('<p itemprop="description">'.$paper['abst'].'</p>');
		if(!empty($paper['link'])) echo ('<a itemprop="url" href="'.$paper['link'].'" target="_blank" rel="external">'.$paper['link'].'</a> <img alt="External link" src="/images/external-link-icon.gif"><br />');
		if(!empty($papertags)){
			echo('<span class="tags" itemprop="keywords">');
			foreach($papertags as $tag) {
				echo('<a href="/search/'.str_replace(" ","+",$tag).'" rel="tag">'.$tag.'</a> ');
			}
			echo('</span><br />');
		}
		echo('<br />');
		echo('<span style="font-size:small;color:grey;">Share: <input type="text" onClick="this.focus();this.select();" value="http://lbnk.tk/'.urlsafe_b64encode("r".$paperid).'"> </span>&emsp;');
		echo('<div class="g-plusone" data-size="medium" data-annotation="inline" data-width="180" data-href="http://www.lecturebank.org/'.$paper['username'].'/talks/'.cleanSlug($paper['title']).'"></div>');
		echo ("</article>");
		
	}
	include('footer.php');
?>