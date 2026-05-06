document.addEventListener("click", function(event) {

    const card = event.target.closest(".room-card");

    if (card) {
        if (card.id === "guarda-roupa") {
            window.location.href = "produtos.php?categoria=guarda-roupa";
        }

        if (card.id === "sofas-e-mesas") {
             window.location.href = "produtos.php?categoria=sofa";
        }
        if (card.id === "escrivaninha") {
            window.location.href = "produtos.php?categoria=escrivaninha";
        }
        if (card.id === "mesa-de-jantar") {
            window.location.href = "produtos.php?categoria=mesa";
        }
    }
});