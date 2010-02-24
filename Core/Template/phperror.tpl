<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<style type="text/css">
			body {
				font-family: Verdana, Helvetica, sans-serif;
			}

			#summary {
				background: #FFFFCC;
				width: 100%;
				color: #FF0000;
				font-weight: bold;
				border: 1px solid #000000;
			}

			#summary td {
				padding: 5px;
			}

			.label {
				color: #000000;
			}

			#error_details {
			}

			.hidden {
				display: none;
			}

			.visible {
				display: block;
			}

		</style>
	</head>
	<body>
		<script language="javascript">
			function toggleVisiblity(id) {
				if (id.className == "hidden") {
					id.className = "visible";
				} else {
					id.className = "hidden";
				}
			}
		</script>
		<h2>An Error occured while processing your page</h2>
		<p>If this is not expected please contact <a href="mailto:{SITE_SUPPORT_EMAIL}">{SITE_SUPPORT_EMAIL}</a></p>
		<div id="details">
			<h3 onClick="toggleVisiblity(summary);"><a name="details" href="#details">Error Details</a></h3>
			<table class="visible" id="summary" summary="SUMMARY NEEDED">
				<tr>
					<td class="label">Date</td>
					<td>{DATE}</td>
				</tr>
				<tr>
					<td class="label">Error Number</td>
					<td>{ERROR_NUMBER}</td>
				</tr>
				<tr>
					<td class="label">Error Type</td>
					<td>{ERROR_TYPE}</td>
				</tr>
				<tr>
					<td class="label">Error Message</td>
					<td>{ERROR_MESSAGE}</td>
				</tr>
				<tr>
					<td class="label">Filename</td>
					<td>{ERROR_FILENAME}</td>
				</tr>
				<tr>
					<td class="label">Line Number</td>
					<td>{ERROR_LINENUMBER}</td>
				</tr>
			</table>
		</div>
		<div id="stacktrace-section">
			<h3 onClick="toggleVisiblity(stacktrace);"><a name="stacktrace" href="#stacetrace">Stack Trace</a></h3>
			<div id="stacktrace" class="visible">
			{STACKTRACE}
			</div>
		</div>
		<div id="code-section">
			<h3 onClick="toggleVisiblity(code);"><a name="code" href="#variables">Code</a></h3>
			<div id="code" class="visible">
				<pre>
{CODE}
				</pre>
			</div>
		</div>
		<div id="memorydump-section">
			<h3 onClick="toggleVisiblity(memorydump);"><a name="code" href="#variables">Memory Dump</a></h3>
			<div id="memorydump" class="visible">
				{MEMORYDUMP}
			</div>
		</div>
	</body>
</html>
