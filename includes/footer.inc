<?php

/* $Id$*/

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">';
	echo '<img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="KwaMoja" title="KwaMoja ' . _('Copyright') . ' &copy; kwamoja.org - ' . date('Y') . '" />';
echo '</div>';

echo '<div id="FooterVersionDiv">';
	echo 'KwaMoja ' ._('version') .' ' . $_SESSION['VersionNumber'] . ' ' . _('Copyright') . ' © 2004 - ' . Date('Y'). ' <a target="_blank" href="http://www.kwamoja.org/kwamoja/doc/Manual/ManualContributors.html">kwamoja.org</a>';
echo '</div>';

echo '<div id="FooterTimeDiv">';
	echo DisplayDateTime();
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>';
echo '</html>';

?>