	function $_(eleid) {
		return document.getElementById(eleid);
	}
	window.onload = function() {
		$_("show-merged").style.display = "block";
		$_("hide-merged").style.display = "none";

		
		
		$_("show-merged").onclick = function() {
			$_("show-merged").style.display = "none";
			$_("hide-merged").style.display = "block";
		}
		$_("hide-merged").onclick = function() {
			$_("show-merged").style.display = "block";
			$_("hide-merged").style.display = "none";
		}
	}	