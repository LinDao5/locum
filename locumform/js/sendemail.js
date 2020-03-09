        function validateForm()
            {
             "use strict";  
                var contactname = $("#contactname").val();
                if (contactname=="" || contactname ==null) {  
                  var err= "contactname";
                }
                var email = $("#email").val();
                if (email=="" || email ==null) {  
                  var err= "email";
                }
                var intRef = $("#intRef").val();
                if (intRef=="" || intRef==null) {  
                  var err= "Intref";
                }
				
                var jobNO = $("#jobNO").val();
                if (jobNO=="" || jobNO==null) {
                   var err= true;
                }
				
                var date = $("#date").val();
                if (date=="" || date==null) { 
                   var err= true;
                }
				
                var rate = $("#rate").val();
                if (rate=="" || rate==null) { 
                   var err= true;
                }
				
                var store = $("#store").val();
                if (store=="" || store==null) { 
                   var err= true;
                }
				
                var timing = $("#timing").val();
                if (timing=="" || timing==null) { 
                   var err= true;
                }
				
                var testTime = $("#testTime").val();
                if (testTime=="" || testTime==null) { 
                   var err= true;
                }
				
                var speReq = $("#speReq").val();
                if (speReq=="" || speReq==null) { 
                   var err= true;
                }
				
              return err;
            }
            $(document).ready(function(){
				"use strict"; 
              $("#button").click(function(e){
                 e.preventDefault();
                  $.ajax({type: "post",
                          url: "http:/ec2-18-163-113-25.ap-east-1.compute.amazonaws.com/locumform/send-email.php",
                          data:$("#form1").serialize(),
                          success:function(result){
                          $("#successmsg").html(result);
                          
                        }}); 
$("#conatctname").val('');
$("#email").val('');
                  $("#intRef").val('');
                  $("#jobNO").val('');
                  $("#date").val('');
                  $("#rate").val('');
                  $("#store").val('');
                  $("#open").val('');
                  $("#close").val('');
                  $("#break").val('');
                  $("#testTime").val('');
                  $("#speReq").val('');
                 // $("#successmsg").remove();
              });
            });
        