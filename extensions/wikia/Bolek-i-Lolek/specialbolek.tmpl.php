<h3>Bolek</h3>

<p>Collection:</p>

<ul>
	<?php foreach ($collection as $page): ?>
		<li>
			<?=Title::newFromID($page)->getText()?>
			[<a href="/wiki/Special:Bolek?action=remove&page_id=<?=$page?>">x</a>]
		</li>
	<?php endforeach; ?>
	<?php if (!sizeof($collection)): ?>
		<li>(empty)</li>
	<?php endif; ?>
</ul>

<p><a href="/wiki/Special:Bolek?action=clear">Clear collection</a></p>

<form method="POST">

<p>Front cover:</p>

<ul>
	<?php foreach ($cover as $c_name => $c_value): ?>
		<li>
			<label for="c_<?=$c_name?>"><?=str_replace("_", " ", $c_name)?></label>
			<input id="c_<?=$c_name?>" name="cover[<?=$c_name?>]" value="<?=htmlspecialchars($c_value)?>"/>
		</li>
	<?php endforeach; ?>
</ul>

<input type="submit" name="action" value="customize"/>

</form>

<h3>Lolek</h3>

<p>Preview <a href="/wiki/Special:Bolek?action=cover">front cover</a> and pdf <a href="/wiki/Special:Bolek?action=print">content</a>.</p>

<script type="text/javascript">
/*<![CDATA[*/
function getPdf() {
	$("#getpdf_result").text("...request sent, please wait...");
	$.get(wgServer, {
		action:    "ajax",
		rs:        "Lolek::getPdf",
		url:       "<?=$url?>",
		bolek_id:  "<?=$bolek_id?>",
		timestamp:  <?=$timestamp?>
	}, function(data, textStatus) {
		if (data.match(/\.pdf$/)) {
			$("#getpdf_result").html("...pdf is ready, please <a href=\"" + data + "\">download it</a>.");
		} else {
			$("#getpdf_result").text("..." + data);
		}
	});
}
/*]]>*/
</script>

<p>
<a href="#" onclick="getPdf();return false;">Get pdf</a>
<span id="getpdf_result"></span>
</p>

<hr />

<p>Debug: (<?=$action?>) <?=$result?> [<?=$bolek_id?>]</p>

