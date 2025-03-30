const stripe = Stripe('pk_test_51PfqINHLhmdc2fDh2vx7PPQR6MLYhdk96a6gTCe3NrXW6FEtxoNzNWUdnrcWOEbM8FJO4npJBzdlxRHU5eJ02TLp00Fvzf07zf');
const elements = stripe.elements();
const cardElement = elements.create('card');

document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button[data-plan]');
    const paymentFormContainer = document.getElementById('payment-form-container');
    const paymentForm = document.getElementById('payment-form');
    let selectedPlan = '';

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            selectedPlan = this.getAttribute('data-plan');
            paymentFormContainer.style.display = 'block';
            cardElement.mount('#card-element');

            // Scroll to payment form
            paymentFormContainer.scrollIntoView({behavior: 'smooth'});
        });
    });

    paymentForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const {token, error} = await stripe.createToken(cardElement);

        if (error) {
            console.error(error);
        } else {
            // Send the token to your server
            fetch('process-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: token.id,
                    plan: selectedPlan
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        text: 'Your plan has been upgraded successfully.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: 'Please try again or contact support.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong. Please try again later.',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
});