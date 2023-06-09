<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php _e('Edit Check'); ?></h4>
</div>

<div class="modal-body">

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label for="name"><?php _e('Name'); ?> *</label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="<?php _e('Name, hostname or other for easy identification'); ?>" value="<?php echo $check['name']; ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="groupid"><?php _e('Group'); ?></label>
                <select class="form-control select2 select2-hidden-accessible" id="groupid" name="groupid" style="width: 100%;" tabindex="-1" aria-hidden="true">
                    <?php foreach ($groups as $group) { ?>
                        <option value='<?php echo $group['id']; ?>' <?php if($group['id'] == $check['groupid']) echo "selected"; ?>><?php echo $group['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="host" id="host-label"><?php _e('Host'); ?> *</label>
                <input type="text" class="form-control" id="host" name="host" required placeholder="<?php _e('Domain or IP Address to check'); ?>" value="<?php echo $check['host']; ?>">
            </div>
        </div>

        <div class="col-md-3" id="port-div">
            <div class="form-group">
                <label for="port"><?php _e('Port'); ?></label>
                <input type="text" class="form-control" id="port" name="port" value="<?php echo $check['port']; ?>">
            </div>
        </div>

        <div class="col-md-3" id="timeout-div">
            <div class="form-group">
                <label for="timeout"><?php _e('Timeout (s)'); ?> *</label>
                <input type="text" class="form-control" id="timeout" name="timeout" required value="<?php echo $check['timeout']; ?>">
            </div>
        </div>

        <div class="col-md-6" id="send-div">
            <div class="form-group">
                <label for="send" id="send-label"><?php if($check['type'] == "dns") _e('DNS Server'); else _e('Send String'); ?></label>
                <input type="text" class="form-control" id="send" name="send" placeholder="" value="<?php echo $check['send']; ?>">
            </div>
        </div>

        <div class="col-md-6" id="expect-div">
            <div class="form-group">
                <label for="expect" id="expect-label"><?php _e('Expected Response'); ?></label>
                <input type="text" class="form-control" id="expect" name="expect" placeholder="" value="<?php echo $check['expect']; ?>">
            </div>
        </div>

    </div>


        <input type="hidden" name="on_map" value="0">

        <div class="row" id="more-form" style="display:none">

            <?php if($isGoogleMaps) { ?>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="lat"><?php _e('Search Address'); ?></label>
                        <input type="text" class="form-control" id="autocomplete" placeholder="<?php _e('Enter address to autofill coordinates'); ?>">
                    </div>
                </div>
            <?php } ?>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="lat"><?php _e('Latitude'); ?></label>
                    <input type="text" class="form-control" id="lat" name="lat" placeholder="<?php _e('Latitude'); ?>" value="<?php echo $check['lat']; ?>" data-validation="number" data-validation-optional="true" data-validation-allowing="range[-90.0;90.0],float,negative" data-validation-error-msg="<?php _e('Invalid Latitude Value'); ?>">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="lng"><?php _e('Longitude'); ?></label>
                    <input type="text" class="form-control" id="lng" name="lng" placeholder="<?php _e('Longitude'); ?>" value="<?php echo $check['lng']; ?>" data-validation="number" data-validation-optional="true" data-validation-allowing="range[-180.0;180.0],float,negative" data-validation-error-msg="<?php _e('Invalid Longitude Value'); ?>">
                </div>
            </div>


        </div>

        <?php if($isGoogleMaps) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="on_map" id="on_map" <?php if($check['on_map'] == "1") echo 'checked="yes"'; ?>  value="1"> <?php _e('Show on map'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>



    <input type="hidden" name="id" value="<?php echo $check['id']; ?>">

    <input type="hidden" name="action" value="editCheck">
    <input type="hidden" name="route" value="<?php echo $_GET['reroute']; ?>">
    <input type="hidden" name="routeid" value="">
    <input type="hidden" name="section" value="">
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-flat btn-default" data-dismiss="modal"><i class="fa fa-times"></i> <?php _e('Cancel'); ?></button>
    <button type="submit" class="btn btn-flat btn-success"><i class="fa fa-save"></i> <?php _e('Save'); ?></button>
</div>

<script type="text/javascript">


	$(".select2").select2({
        placeholder: "<?php _e('Please select'); ?>"
    });


    <?php if($check['type'] == "icmp") { ?>
        $("#port-div").fadeOut();
        $("#send-div").fadeOut();
        $("#expect-div").fadeOut();
    <?php } ?>

    <?php if($check['type'] == "blacklist") { ?>
        $("#port-div").fadeOut();
        $("#send-div").fadeOut();
        $("#expect-div").fadeOut();
        $("#timeout-div").fadeOut();
        $("#host-label").text("<?php _e('IP address to check *'); ?>");
    <?php } ?>

    <?php if($check['type'] == "dns") { ?>
        $("#port-div").fadeOut();
        $("#host-label").text("<?php _e('Query *'); ?>");
    <?php } ?>

    <?php if($check['type'] == "tcp") { ?>
        $("#send-div").fadeOut();
        $("#expect-div").fadeOut();
    <?php } ?>

    <?php if($check['type'] == "udp") { ?>
        $("#send-div").fadeOut();
        $("#expect-div").fadeOut();
    <?php } ?>

    <?php if($check['type'] == "callback") { ?>
        $("#send-div").fadeOut();
        $("#expect-div").fadeOut();
        $("#timeout-div").fadeOut();
        $("#port-div").fadeOut();
        $("#host-label").text("<?php _e('Secret key *'); ?>");
    <?php } ?>

</script>



<?php if($isGoogleMaps) { ?>
    <script>

        $.validate({
          decimalSeparator : '.'
        });

        $(document).ready(function(){
            $("#on_map").change(function(){
                if(this.checked) { $("#more-form").slideDown(); }
                else { $("#more-form").slideUp(); }
            });
        });

        <?php if($check['on_map'] == "1") { ?>
            $("#more-form").slideDown();
        <?php } ?>

        var autocomplete;

        function initAutocomplete() {
            // Create the autocomplete object, restricting the search to geographical
            // location types.
            autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
            {types: ['geocode']});

            // When the user selects an address from the dropdown, populate the address
            // fields in the form.
            autocomplete.addListener('place_changed', fillInAddress);
        }

        function fillInAddress() {
            // Get the place details from the autocomplete object.
            var place = autocomplete.getPlace();

            // Populate values
            $( "#lat" ).val( place.geometry.location.lat() );
            $( "#lng" ).val( place.geometry.location.lng() );
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo getConfigValue("google_maps_api_key"); ?>&libraries=places&callback=initAutocomplete" async defer></script>
<?php } ?>
