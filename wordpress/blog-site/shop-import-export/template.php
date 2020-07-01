<?php
/**
 * Template in Admin page
 *
 * $this->equivalent array is in shop-import-export.php
 */

// css
// wp_enqueue_style( 'shop-import-export-css', SHOP_PLUGIN_URL . 'assets/css/shop-import-export.css', array(), false, 'all' );

// js
wp_enqueue_script( 'shop-file-uploader', SHOP_PLUGIN_URL . 'assets/js/shop-file-uploader.js', array( 'jquery' ), null, true );
wp_enqueue_script( 'shop-import-export-js', SHOP_PLUGIN_URL . 'assets/js/shop-import-export.js', array( 'jquery' ), null, true );
$uploads_dir = wp_upload_dir();
?>
<div class="wrap sie-body">
	<h2>インポートエクスポート</h2>

	<!-- export start -->
	<div class="bulk-update-action sie-export">
		<h3>エクスポート</h3>
		<div class="sie-field sie-marginTop20">
			<p>※データ件数が多い場合、出力に時間がかかります。</p>
			<p><input type="button" class="sie-btn sie-btn-red" id="export" value="店舗データエクスポート"></p>
			<p><input type="button" class="sie-btn sie-btn-red" id="custom-sched-export" value="カスタム営業時間データエクスポート"></p>

		</div>
		<form id="sie-custom-sched-form" method="post" action="">
			<input type="hidden" name="custom-sched-file" value="<?php echo basename( SHOP_SCHED_CSV_FULL_FILEPATH );?>">
		</form>

		<!-- The Modal start -->
		<div id="loader" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
			   <img src="<?php echo SHOP_PLUGIN_URL;?>assets/img/loading.gif">
			</div><!-- Modal content end -->
		</div><!-- The Modal end -->
		<div id="sie-hidden-form"></div>
		<div id="confirmExportModal" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
				<div class="sie-modal-content-inside">
					<div class="sie-container">
						<div class="sie-modal-container-inside sie-modal-body">
							<p>店舗データをエクスポートします。よろしいですか？</p>
						</div>
						<div class="sie-modal-container-inside sie-modal-footer">
							<button type="button" class="sie-btn-modal sie-btn-export">店舗データエクスポート</button>
							<button type="button" class="sie-btn-modal sie-btn-cancel">キャンセル</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal content end -->
		</div>
		<div id="confirmCustomSchedExportModal" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
				<div class="sie-modal-content-inside">
					<div class="sie-container">
						<div class="sie-modal-container-inside sie-modal-body">
							<p>カスタム営業時間データをエクスポートします。よろしいですか？</p>
						</div>
						<div class="sie-modal-container-inside sie-modal-footer">
							<button type="button" class="sie-btn-modal sie-btn-custom-sched-export">カスタム営業時間データエクスポート</button>
							<button type="button" class="sie-btn-modal sie-btn-cancel">キャンセル</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal content end -->
		</div>
		<!-- The Modal end -->
	<input type="hidden" name="shop_csv_filepath" value="<?php echo SHOP_CSV_FULL_FILEPATH;?>">
	<input type="hidden" name="shop_download_file" value="<?php echo SHOP_DOWNLOAD_FILE;?>">
	</div>
	<!-- export end -->

	<!-- import start -->
	<div class="bulk-update-action sie-import">
		<h3>インポート</h3>
		<input type="hidden" name="wp_uploads_filepath" value="<?php echo $uploads_dir['basedir'];?>">
		<input type="hidden" name="site_url" value="<?php echo site_url();?>">
		<div class="sie-field">
			<label style="display: block;">ファイル</label><br>
			<?php
				$name = 'shop_import_file';
				$value = '';

				$image      = ' button">ファイルを選択';
				$image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
				$display    = 'none'; // display state ot the "Remove image" button

				if ( $image_attributes = wp_get_attachment_url( ( int )$value ) ) {
					$display = 'block';
					$file_name = pathinfo( $image_attributes, PATHINFO_EXTENSION );
					$image = '">';
					$image .= '<div class="sie-thumbnail">';
					$image .= '<div class="sie-centered">';
					$image .= '<img src="'.site_url( '/' ).'wp-includes/images/media/document.png"" / alt="'.$value.'">';
					$image .= '</div>';
					$image .= '<div class="sie-filename">';
					$image .= '<div>'.get_the_title( $value ).'.'.$file_name.'</div>';
					$image .= '</div>';
					$image .= '</div>';
				}

				$file_path = get_attached_file( $value );
			?>
				<div style="width:150px;">
					<a href="#" style="text-decoration:none" class="sie_upload_file_button<?php echo $image;?></a>
					<input type="hidden" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>" />
					<a href="#" class="sie_remove_image_button" style="display:inline-block;display:<?php echo $display;?>">ファイルを削除</a>
				</div>
			<span class="sie-file-error sie-file-error-js"></span>
		</div>
		<div class="sie-field sie-marginTop20">
			<input type="button" class="sie-btn sie-btn-blue" id="import" value="インポート">
		</div>

		<!-- The Modal start -->
		<div id="loader" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
			   <img src="<?php echo SHOP_PLUGIN_URL;?>assets/img/loading.gif">
			</div><!-- Modal content end -->
		</div><!-- The Modal end -->
		<div id="sie-hidden-form"></div>
		<div id="confirmImportModal" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
				<div class="sie-modal-content-inside">
					<div class="sie-container">
						<div class="sie-modal-container-inside sie-modal-body">
							<p>　インポートします。よろしいですか？</p>
						</div>
						<div class="sie-modal-container-inside sie-modal-footer">
							<button type="button" class="sie-btn-modal sie-btn-import">インポート</button>
							<button type="button" class="sie-btn-modal sie-btn-cancel">キャンセル</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal content end -->
		</div>
		<!-- The Modal end -->

	</div>
	<!-- import end -->

	<!-- import result start -->
	<div class="bulk-update-action sie-import-res">
		<?php if ( file_exists( $log ) ):
			$csv_file = get_option( 'shop-csv-file' );
			if ( $csv_file !== false ):
		?>
		<div class="sie-marginTop55">
			<h3>インポートしたファイル: <?php echo basename( $csv_file );?></h3>
		</div>
		<?php endif;?>
		<div class="sie-dl">
			<h3>インポート結果: <span><?php echo date( 'Y-m-d H:i:s', filemtime( $log ) );?></span></h3>
			<p class="p-btn"><input type="button" class="sie-btn sie-btn-violet" id="download" value="ダウンロード"></p>
		</div>
		<!-- The Modal start -->
		<div id="loader" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
			   <img src="<?php echo SHOP_PLUGIN_URL;?>assets/img/loading.gif">
			</div><!-- Modal content end -->
		</div><!-- The Modal end -->
		<div id="sie-dl-hidden-form">
			<form id="dl-form" method="post" action="">
				<input type="hidden" name="log_file" value="<?php echo $log;?>">
			</form>
		</div>
		<div id="confirmDownloadLogModal" class="sie-modal">
			<!-- Modal content -->
			<div class="sie-modal-content">
				<div class="sie-modal-content-inside">
					<div class="sie-container">
						<div class="sie-modal-container-inside sie-modal-body">
							<p>ダウンロードしてもよろしいですか？</p>
						</div>
						<div class="sie-modal-container-inside sie-modal-footer">
							<button type="button" class="sie-btn-modal sie-btn-download">ダウンロード</button>
							<button type="button" class="sie-btn-modal sie-btn-cancel">キャンセル</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal content end -->
		</div>
		<!-- The Modal end -->

		<div class="sie-table-div">
			<?php
				if ( strpos( $log, 'sched' ) > -1 ):
			?>
				<table class="sie-table sie-sched-table">
					<tr>
						<th width="200px">ID</th>
						<th width="200px">店舗ID</th>
						<th width="200px">名前</th>
						<th width="320px">ステータス</th>
						<!-- <th width="400px">クエリ</th> -->
					</tr>
				<?php
					$handle = fopen( $log, 'r' );
					if ( $handle !== false ) :
						$line = 0;
						while (($data = fgets($handle, 1000)) !== false) :
							if ( $line > 100 ) {
								continue;
							}
							$status = strpos( $data, '{Status: OK}' ) > -1?'<span class="sie-ok">OK</span>':'<span class="sie-ng">NG</span>';
							$pattern = '/{"item_id"(.*?) ----end./';
							preg_match_all( $pattern, $data, $match );
							if ( !empty( $match[0][0] ) ) :
								$metadata = json_decode( str_replace( ' ----end.', '', $match[0][0] ) );
								$name = get_field( 'store_name', $metadata->postid );
				?>
					<tr>
						<td><?php echo $metadata->item_id;?></td>
						<td><?php echo $metadata->postid;?></td>
						<td><?php echo $name;?></td>
						<td><?php echo $status;?></td>
						<!-- <td><?php //echo json_encode( $metadata->sql );?></td> -->
					</tr>
				<?php
							endif;
						$line++;
						endwhile;
					endif;
				?>
				</table>
			<?php
				else :
			?>
				<table class="sie-table">
					<tr>
						<th width="70px">店舗ID</th>
						<th width="300px">名前</th>
						<th width="200px">メタキーワード</th>
						<th width="500px">メタバリュー</th>
						<th width="100px">タイプ</th>
						<th width="120px">ステータス</th>
						<!-- <th width="600px">クエリ</th> -->
					</tr>

				<?php
					$handle = fopen( $log, 'r' );
					$equivalent = SHOP_SITE_TYPE === 'miyagi'?$this->miyagi_equivalent:$this->equivalent;
					if ( $handle !== false ) :
						$line = 0;
						while ( ( $data = fgets($handle, 1000 ) ) !== false ) :
							if ( $line > 100 ) {
								continue;
							}
							$status = strpos( $data, '{Status: OK}' ) > -1?'<span class="sie-ok">OK</span>':'<span class="sie-ng">NG</span>';
							$type = strpos( $data, '「update」' ) === false?'新規追加':'更新';
							$pattern = '/{"postid"(.*?) ----end./';
							preg_match_all( $pattern, $data, $match );
							if ( !empty( $match[0][0] ) ) :
								$metadata = json_decode( str_replace( ' ----end.', '', $match[0][0] ) );
								$metakey = isset( $equivalent[$metadata->metakey] )?$equivalent[$metadata->metakey]:$metadata->metakey;
								$name = get_field( 'store_name', $metadata->postid );
				?>
					<tr>
						<td><?php echo $metadata->postid;?></td>
						<td><?php echo $name;?></td>
						<td><?php echo $metakey;?></td>
						<td><?php echo $metadata->metavalue;?></td>
						<td><?php echo $type;?></td>
						<td><?php echo $status;?></td>
						<!-- <td><?php //echo $metadata->sql;?></td> -->
					</tr>
				<?php
							endif;
						$line++;
						endwhile;
					endif;
					fclose( $handle );
				?>
				</table>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<!-- import result end -->
	<input type="hidden" name="shop_js_path" value="<?php echo $shop_js_path;?>">
</div>
