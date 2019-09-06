<span><?php echo isset($msg) ? $msg : ''; ?></span>
<?php if(isset($button_confirm)) : ?>
<form action="<?php echo $action; ?>" method="post">
  <input type="hidden" name="aksi" value="bayar" />
  <input type="hidden" name="orderid" value="<?php echo $ap_itemcode; ?>" />
  <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>
<?php endif; ?>