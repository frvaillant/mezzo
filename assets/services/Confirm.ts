

function customConfirm(message: string) {
    return new Promise((resolve) => {

        const overlay = document.createElement("div");
        overlay.id = "custom-confirm-overlay";

        const box = document.createElement("div");
        box.id = "custom-confirm-box";

        const msg = document.createElement("p");
        msg.textContent = message;

        const btnYes = document.createElement("button");
        btnYes.className = "yes";
        btnYes.textContent = "Oui";

        const btnNo = document.createElement("button");
        btnNo.className = "no";
        btnNo.textContent = "Non";

        box.appendChild(msg);
        box.appendChild(btnYes);
        box.appendChild(btnNo);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        btnYes.addEventListener("click", () => {
            document.body.removeChild(overlay);
            resolve(true);
        });

        btnNo.addEventListener("click", () => {
            document.body.removeChild(overlay);
            resolve(false);
        });
    });
}

export default customConfirm
