<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Merchant</title>
    <meta name="viewport" content="width=device-width" ,="" initial-scale="1.0/">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrom=1">
    <script src="js/jquery-1.8.3.min.js"></script>
    <script id = "myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

</head>

<body>

    <button id="bKash_button">Pay With bKash</button>

    <script type="text/javascript">

        var accessToken='';
        $(document).ready(function(){
            $.ajax({
                url: "token.php",
                type: 'POST',
                contentType: 'application/json',
                success: function (data) {
                    console.log('got data from token  ..');
                    console.log(JSON.stringify(data));

                    accessToken=JSON.stringify(data);        
                    //localStorage.setItem("accessToken", accessToken);  
                    setTimeout(function(){
                        saveBkashRepose(accessToken);
                    },3000);          
                },
                error: function(){
                  console.log('error');

              }
          });

            var paymentConfig={
                createCheckoutURL:"createpayment.php",
                executeCheckoutURL:"executepayment.php",
                queryCheckoutURL:"paymentQuery.php",
                searchCheckoutURL:"search.php",
            };


            var paymentRequest;
            paymentRequest = { amount:'105',intent:'sale'};
            console.log(JSON.stringify(paymentRequest));

            bKash.init({
                paymentMode: 'checkout',
                paymentRequest: paymentRequest,
                createRequest: function(request){
                    console.log('=> createRequest (request) :: ');
                    console.log(request);


                    //var accessToken = localStorage.getItem("accessToken");
                    $.ajax({
                        url: paymentConfig.createCheckoutURL+"?amount="+paymentRequest.amount+"&accessToken="+accessToken,
                        type:'GET',
                        contentType: 'application/json',
                        success: function(data) {
                            console.log('got data from create  ..');
                            console.log('data ::=>');
                            console.log(JSON.stringify(data));

                            var obj = JSON.parse(data);

                            var sendData1=JSON.stringify(data);
                            setTimeout(function(){
                                saveBkashRepose(sendData1);
                            },3000);



                            if(data && obj.paymentID != null){
                                paymentID = obj.paymentID;
                                bKash.create().onSuccess(obj);
                            }
                            else {
                               console.log('error');
                               bKash.create().onError();
                           }
                       },
                       error: function(){
                          console.log('error');
                          bKash.create().onError();
                      }
                  });
                },

                executeRequestOnAuthorization: function(){
                    console.log('=> executeRequestOnAuthorization');
                    $.ajax({
                        url: paymentConfig.executeCheckoutURL+"?paymentID="+paymentID+"&accessToken="+accessToken,
                        type: 'GET',
                        contentType:'application/json',
                        success: function(data){
                            console.log('got data from execute  ..');
                            console.log('data ::=>');
                            console.log(JSON.stringify(data));

                            data = JSON.parse(data);
                            if(data && data.paymentID != null){
                                //alert(JSON.stringify(data));

                                var sendData=JSON.stringify(data);

                                setTimeout(function(){
                                    saveBkashRepose(sendData);
                                },3000);


/*
                                $.ajax({
                                    url: paymentConfig.queryCheckoutURL+"?paymentID="+paymentID+"&accessToken="+accessToken,
                                    type: 'GET',
                                    contentType:'application/json',
                                    success: function(data){
                                        console.log('got data from Query  ..');
                                        console.log('data ::=>');
                                        console.log(JSON.stringify(data));
                                        data = JSON.parse(data);
                                        if(data && data.trxID != null){ 
                                            var sendData=JSON.stringify(data);
                                            setTimeout(function(){
                                                saveBkashRepose(sendData);
                                            },32000);

                                            $.ajax({
                                                url: paymentConfig.searchCheckoutURL+"?paymentID="+data.trxID+"&accessToken="+accessToken,
                                                type: 'GET',
                                                contentType:'application/json',
                                                success: function(data){
                                                    console.log('got data from Search  ..');
                                                    console.log('data ::=>');
                                                    console.log(JSON.stringify(data));
                                                    data = JSON.parse(data);
                                                    
                                                    var sendData=JSON.stringify(data);
                                                    setTimeout(function(){
                                                        saveBkashRepose(sendData);
                                                    },3000);                                                    
                                                },
                                                error: function(){
                                                    bKash.execute().onError();
                                                }
                                            });



                                        }
                                        else 
                                        {
                                            if(data.errorCode == 2029){
                                                alert(data.errorMessage);
                                            }
                                            bKash.execute().onError();
                                        }
                                    },
                                    error: function(){
                                        bKash.execute().onError();
                                    }
                                });*/



                            }
                            else 
                            {
                                if(data.errorCode == 2029){
                                    alert(data.errorMessage);
                                }
                                bKash.execute().onError();
                            }
                        },
                        error: function(){
                            bKash.execute().onError();
                        }
                    });
}
});

console.log("Right after init ");


});

function saveBkashRepose(sendData) {
    var fdata = JSON.parse(sendData);
           // alert(fdata.paymentID);

           $.post('https://shl.com.bd/api/appapi/bkash-marchant-callback-url', 
           {
            'data': fdata
        }, function(data, textStatus, xhr) {
               // alert(data);
                //alert('here 3');
                //window.location.href = "success.html";       
            });
       }

       function callReconfigure(val){
        bKash.reconfigure(val);
    }

    function clickPayButton(){
        $("#bKash_button").trigger('click');
    }


    let searchParams = new URLSearchParams(window.location.search)
    let param = searchParams.get('sent')
    if(param == "app"){
        setTimeout(function(){
         $("#bKash_button").trigger('click');
     },5000);

    }


</script>

</body>
</html>
