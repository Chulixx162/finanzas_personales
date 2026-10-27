
 // public/js/alertas.js
async function alerta(
    title,
    text,
    icon,
    confirmButtonText,
    cancelButtonText,
    confirmButtonColor,
    cancelButtonColor,
    background,
    color,
    deleteUrl
) {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: cancelButtonColor,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        background: background,
        color: color
    });

    if (result.isConfirmed) {
        window.location.href = deleteUrl;
    }
    return result.isConfirmed;
}
