document.getElementById('buyButton').addEventListener('click', function() {
    const paymentMethods = document.getElementsByName('paymentMethod');
    let paymentSelected = false;

    for (let i = 0; i < paymentMethods.length; i++) {
      if (paymentMethods[i].checked) {
        paymentSelected = true;
        break;
      }
    }
  
  
    if (!paymentSelected) {
      document.getElementById('errorMessage').classList.remove('d-none');
    } else {
      document.getElementById('errorMessage').classList.add('d-none');
      alert('Compra realizada con éxito');
    }
  });