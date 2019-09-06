<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_security; ?></td>
            <td><input type="text" name="ipaymu_security" value="<?php echo $ipaymu_security; ?>" />
              <?php if ($error_security) { ?>
              <span class="error"><?php echo $error_security; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_paypal; ?></td>
            <td><input type="text" name="ipaymu_paypal" placeholder="ex: youremail@domain.com" value="<?php echo $ipaymu_paypal; ?>" />
              <?php if ($error_paypal) { ?>
              <span class="error"><?php echo $error_paypal; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_ipaymu_rate; ?></td>
            <td><input type="text" name="ipaymu_rate" placeholder="ex: 12000" value="<?php echo $ipaymu_rate; ?>" />
          </tr>
          <tr>
            <td><?php echo $entry_invoice; ?></td>
            <td><input type="text" name="ipaymu_inv_paypal" placeholder="ex: INV-" value="<?php echo $ipaymu_inv_paypal; ?>" />
              <?php if ($error_inv_paypal) { ?>
              <span class="error"><?php echo $error_inv_paypal; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="ipaymu_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ipaymu_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="ipaymu_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $ipaymu_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="ipaymu_status">
                <?php if ($ipaymu_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="ipaymu_sort_order" value="<?php echo $ipaymu_sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 