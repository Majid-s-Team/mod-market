<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
        <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
        <form id="payment-form">
        <div id="card-element">
            <!-- A Stripe Element will be inserted here. -->
        </div>

        <!-- Used to display form errors. -->
        <div id="card-errors" role="alert"></div>

        <button type="submit">Submit Payment</button>
    </form>
    <script>
            const stripe = Stripe('{{ env('STRIPE_PUBLISHED_KEY') }}'); // Replace with your Stripe publishable key
    const elements = stripe.elements();
        const cardElement = elements.create('card');
            cardElement.mount('#card-element'); // Assuming you have a div with id="card-element"
                const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const { token, error } = await stripe.createToken(cardElement);

        if (error) {
            // Display error to the user
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            // Send the token.id to your server for further processing (e.g., creating a charge)
            console.log('Stripe Token:', token);
            // Example: Append token to a hidden input and submit the form
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            form.submit(); // Submit the form to your server
        }
    });
    </script>
</body>
</html>