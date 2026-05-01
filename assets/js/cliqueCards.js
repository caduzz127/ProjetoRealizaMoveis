document.addEventListener("click", function(event) {

    const card = event.target.closest(".room-card");

    if (card) {
        if (card.id === "guarda-roupa") {
            window.location.href = "guarda-roupa.php";
        }

        if (card.id === "sofas-e-mesas") {
             window.location.href = "sofa.php";
        }
        if (card.id === "escrivaninha") {
            window.location.href = "escrivaninha.php";
        }
        if (card.id === "mesa-de-jantar") {
            window.location.href = "mesa.php";
        }
    }
});