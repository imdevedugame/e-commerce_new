<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midtrans Payment</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body>
    <script type="text/javascript">
        snap.pay(JSON.parse('@json($snapToken)'), {
            onSuccess: function(result) {
        alert("Payment Successful!");
        fetch("{{ route('checkout.success') }}", {
            method: "POST",  // Use POST to send the payment data to the server
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(result)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If success, redirect to the success page
                window.location.href = data.redirect_url;
            } else {
                alert(data.error);
                window.location.href = "{{ route('checkout.failed') }}";
            }
        });
    },
    onPending: function(result) {
        alert("Payment Pending!");
        window.location.href = "{{ route('checkout.pending') }}";
    },
    onError: function(result) {
        alert("Payment Failed!");
        window.location.href = "{{ route('checkout.failed') }}";
    }
});

    </script>
</body>
</html>
