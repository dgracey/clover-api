  <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Midwest Tile</title>
        <link rel="stylesheet" href="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/styles/style.css'; ?>">
    </head>
    <body>

    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
        <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/img/mwt-icon.png'; ?>" width="10%"/>
            <h4>Payment processing...</h4>
        </div>

    </div>

    <div class="screen" id="success" style="display:none">
        <div id="payment-status" style="padding: 15px; background: #2d2d2d;">
            <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/img/confirm-icon.svg'; ?>" width="10%"/>
            <h2 style="color:white;">Payment Successful</h2>
            <div id="header" style="color:white;">
                <p>Midwest Tile</p>
                <p>200 W Industrial Lake Drive, Lincoln, NE 68528</p>
                <p>402-476-2542</p>
            </div>
        </div>
        <div id="payment-details" style="padding: 20px 20px;">

        <div id="invoice-info">
            <h5 style="margin: 0">Invoice Number</h5>
            <h5 id="detail-invoice" style="margin:0;"> 1231241242 </h5>
        </div>

        <div id="totals">
            <p><span>Subtotal:</span> <span id="detail-subtotal">$57.50</span></p>
            <p><span>Fees:</span> <span id="detail-fees">$5.75</span></p>
            <p class="total"><span>Total:</span> <span id="detail-total">$63.25</span></p>
        </div>

        <div id="customer-info">
            <h4>Payment Details</h4>
            <p id="detail-card">Card: VISA 4242</p>
            <p id="detail-paymentID">Payment ID: 1ASF212</p>
            <p id="detail-date">November 29, 2024, 9:23 AM</p>
            <p id="detail-authID">Auth ID: 238193</p>
            <p id="detail-refID">Reference ID: 1293832731</p>
        </div>

        <div id="footer">
            <p>A copy of this reciept has been sent to your email.</p>
        </div>
        </div>
    </div>

    <div class="screen"  id="failed" style="display:none">
        <div id="payment-status" style="padding: 15px; background: #2d2d2d;">
        <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/img/close-red-icon.svg'; ?>" width="10%"/>
            <h2 style="color:white;">Payment Failed</h2>
            <div id="header" style="color:white;">
                <p>Midwest Tile</p>
                <p>200 W Industrial Lake Drive, Lincoln, NE 68528</p>
                <p>402-476-2542</p>
            </div>
        </div>
        <div id="payment-details" style="padding: 20px 20px;">

        <h4 style="text-align: center";> There has been an issue with this payment </h4>

        <p style="text-align:center" id="error"></p>
        <p style="text-align:center" id="message"></p>

        <div id="footer">
            <p>You have not been charged for this transaction.</p>
        </div>
        </div>
    </div>

    <form name="paymentForm" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" id="ajax-payment-form">

        <div class="form-row">
        <a href="https://midwesttile.com"><img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/img/mwt-logo.png'; ?>" style="padding: 15px; background: #2d2d2d; max-width: 100%;"/></a>
        </div>

        <div class="form-row">
            <p style="text-align:center; font-size: 12px; color: #2d2d2d;">Fields marked with an * are required</p>
        </div>

        <!-- Email -->
        <div class="form-row">
            <label for="email">Email</label>
            <p id="email-required" class="required-text">This field is required</p>
            <input type="text" step="0.01" id="email" name="email" required placeholder="johndoe@example.com">
        </div>
        <div class="form-row flex-row">
            <div>
                <label for="amount">Amount</label>
                <p id="amount-required"class="required-text">This field is required</p>
                <input type="number" step="0.01" id="amount" name="amount" required placeholder="250.62">
            </div>
            <div>
                <label for="invoice">Invoice Number</label>
                <p id="invoice-required"class="required-text">This field is required</p>
                <input type="text" id="invoice" name="invoice" required placeholder="17283874" maxlength="12">
            </div>
        </div>

        <div class="form-row">
            <label for="cardholder-name">Cardholder Name</label>
            <p id="cardholder-name-required" class="required-text">This field is required</p>
            <input type="text" id="cardholder-name" name="cardholder_name" required placeholder="John Doe">
        </div>
        <div class="form-row">
            <label for="card-number">Card Number</label>
            <p id="card-number-required" class="required-text">This field is required</p>
            <input type="text" id="card-number" name="card_number" required placeholder="4242424242424242" maxlength="16">
        </div>
        <div class="form-row flex-row">
            <div>
                <label for="exp">Expiration</label>
                <p id="exp-required" class="required-text">This field is required</p>
                <input type="text" id="exp" name="exp" required placeholder="05/29" maxlength="5">
            </div>
            <div>
                <label for="cvv">CVV (Security Code)</label>
                <p id="cvv-required" class="required-text">This field is required</p>
                <input type="text" id="cvv" name="cvv" required placeholder="123" maxlength="4">
            </div>
        </div>
        <div class="form-row">
            <label for="street-address">Address 1</label>
            <p id="address-1-required" class="required-text">This field is required</p>
            <input type="text" id="address-1" name="address_1" required placeholder="123 Main St">
        </div>
        <div class="form-row">
            <label id="address_input" for="street-address">Address 2</label>
            <input type="text" id="address-2" name="address_2" placeholder="Apt 1, etc.">
        </div>
        <div class="form-row">
            <label for="city">City</label>
            <p id="city-required" class="required-text">This field is required</p>
            <input type="text" id="city" name="city" required placeholder="City Name">
        </div>
        <div class="form-row flex-row">
            <div>
                <label for="state">State</label>
                <p id="state-required" class="required-text">This field is required</p>
                <input type="text" id="state" name="state" required placeholder="State">
            </div>
            <div>
                <label for="zip">ZIP Code</label>
                <p id="zip-required" class="required-text">This field is required</p>
                <input type="text" id="zip" name="zip" required placeholder="12345" maxlength="10">
            </div>
        </div>
        <div class="form-row">
            <p style="text-align:center"><b>Please note:</b> </p>
            <p style="text-align:center">If you are paying with a credit card, there is an extra 3% transaction fee that will be applied to your charge, bringing your total charge to <b>$<span id="ccFee">0.00</span></b></p>
        </div>
        <input type="hidden" name="action" placeholder="start_transaction">
        <div class="form-row" style="padding-top:15px;">
            <button type="button" id="submitButton">Pay Now</button>
        </div>
        <div class="form-row">
            <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'templates/img/card_types.png'; ?>" alt="Accepted card types: Visa, Mastercard, American Express, Discover." style="max-width:100%"/>
        </div>


    </form>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>

    function validateForm(formFields) {

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const expRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;

        const keys = ["email","amount", "invoice", "cardholder-name", "card-number", "exp", "cvv", "address-1", "address-2", "city", "state", "zip"];

        for (let x = 0; x < formFields.length; x++){

            var id = keys[x];
            var field = "#" + id + "-required"
            let e = document.getElementById(id);

            if (x == 0 && !emailRegex.test(e.value) && formFields[x] != ""){

                e.classList.add("requiredField");
                $(field).text("Please use a valid email address");
                $(field).css("visibility","visible");
                e.focus();
                return false;

            }
            if (x == 5 && !expRegex.test(e.value)){

                e.classList.add("requiredField");
                $(field).css("visibility","visible");
                e.focus();
                return false;
            }
            if (formFields[x] == "" && x != 8){

                e.classList.add("requiredField");
                $(field).css("visibility","visible");
                e.focus();
                return false;
            }
        }

        return true;
    }

    $("input").change(function(){
        $(this).removeClass("requiredField");

        id = $(this).attr('id');

        $("#" + id + "-required").css("visibility","hidden");

        if (id="email"){
            $("#" + id + "-required").text("This field is required");
        }
    })

    $("#amount").change(function(){
        $(this).val(parseFloat($(this).val()).toFixed(2));
        $("#ccFee").text(($("#amount").val() * 1.03).toFixed(2));
    })

    $('#exp').on('input', function(e) {

        let value = $(this).val().replace(/[^0-9]/g, '');

        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }

        $(this).val(value.substring(0, 5));
    });

    $('#submitButton').on("click", function(e){

        const emailField = document.getElementById('email');
        const expField = document.getElementById('exp');
        
        // Regular expressions for validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const expRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;

        var amount = $("#amount").val();
        var invoice = $("#invoice").val();
        var exp = $("#exp").val();
        var cardholder_name = $("#cardholder-name").val();
        var card_number = $("#card-number").val();
        var cvv = $("#cvv").val();
        var address_1 = $("#address-1").val();
        var address_2 = $("#address-2").val();
        var city = $("#city").val();
        var state = $("#state").val();
        var zip = $("#zip").val();
        var email = $("#email").val();

        const fields = [];

        fields.push(email, amount, invoice, cardholder_name, card_number,exp, cvv, address_1, address_2, city, state, zip)

        if (validateForm(fields) == false){
            return;
        };

        $("#myModal").css("display", "flex");

        $.ajax({ 
            data: {_ajax_nonce: "<?php echo wp_create_nonce( '_ajax_nonce' )?>", 
                action: 'start_transaction', 
                amount:amount,
                invoice:invoice,
                exp:exp,
                cardholder_name:cardholder_name,
                card_number:card_number,
                cvv:cvv,
                address_1:address_1,
                address_2:address_2,
                city:city,state:state,
                zip:zip,
                email:email},
            type: 'post',
            url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
            success: function(response) {

                json = JSON.parse(response);

                console.log(json);

                $("#myModal").hide();

                if (json["status"] == "succeeded"){

                    subtotal_initial = json["amount"];
                    fees_initial = 0;

                    if (json.hasOwnProperty("additional_charges")){
                        fees_initial = json['additional_charges'][0]['amount'];
                    }

                    total_initial = (subtotal_initial + fees_initial);

                    subtotal = (subtotal_initial / 100);
                    fees = fees_initial;
                    if (fees != 0){
                        fees = (fees / 100)
                    }

                    total = ((subtotal_initial + fees_initial) / 100);

                    card = json["source"]["brand"] + " " + String(json["source"]["last4"]);

                    unixTimestamp = json["created"];
                    date = new Date(unixTimestamp);

                    day = date.toLocaleString('en-US', {month: 'long', day: 'numeric', year: 'numeric'})
                    time = date.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});

                    $('#detail-invoice').text(json["external_reference_id"])
                    $('#detail-subtotal').text(subtotal.toFixed(2))
                    $('#detail-fees').text(fees.toFixed(2))
                    $('#detail-total').text(total.toFixed(2))
                    $('#detail-card').text("Card: " + card)
                    $('#detail-paymentID').text("Payment ID: " + json["id"])
                    $('#detail-date').text(day + ", " + time)
                    $('#detail-refID').text("Reference ID: " + json['ref_num'])
                    $('#detail-authID').text("Auth ID: " + json['auth_code'])

                    $("#ajax-payment-form").hide();
                    $("#success").show();
                    } else{
                        console.log(json);
                        $('#error').text(json["message"])
                        $('#message').text(json["error"]["message"])
                        $("#myModal").hide();
                        $("#ajax-payment-form").hide();
                        $("#failed").show();
                    }
                
            },
            error: function(data){
                $('#error').text(json["message"])
                $('#message').text(json["error"]["message"])
                $("#ajax-payment-form").hide();
                $("#failed").show();
            }

        });

    });

    // Function to validate email format
    function isValidEmail(email) {
        const emailRegex = /^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
    }

    // Function to validate credit card expiration date format and validity
    function isValidExpirationDate(expirationDate) {
        // Regular expression to match MM/YY format
        const dateRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;

        if (!dateRegex.test(expirationDate)) {
            return false;
        }

        // Extract month and year from the input
        const [month, year] = expirationDate.split('/').map(Number);

        // Get the current date
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // JavaScript months are 0-based
        const currentYear = currentDate.getFullYear() % 100; // Get last two digits of the year

        // Check if the date is in the future
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return false;
        }

        return true;
    }
    </script>
    </body>
    </html>