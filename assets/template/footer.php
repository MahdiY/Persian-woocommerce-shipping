</div>
<script type="text/javascript" src="<?php echo plugin_dir_url( PWS_FILE ); ?>assets/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript"
        src="<?php echo plugin_dir_url( PWS_FILE ); ?>assets/js/jquery-barcode-2.0.2.min.js"></script>
<script>
	$(".post_barcode span").each(function ( index ) {
		let post_barcode = $(this).html();
		$(this).barcode(post_barcode.trim(), "code128", { barWidth: 1, barHeight: 30 });
	});
	print();
</script>
</body>
</html>