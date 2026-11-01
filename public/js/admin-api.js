const API_URL = "/api";

function adminToken() {
    return localStorage.getItem("admin_token"); // cambia la clave si la tuya se llama distinto
}

function esc(value) {
    const div = document.createElement("div");
    div.textContent = value ?? "";
    return div.innerHTML;
}

async function apiFetch(path, options = {}) {
    const res = await fetch(`${API_URL}${path}`, {
        ...options,
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: `Bearer ${adminToken()}`,
            ...(options.headers || {}),
        },
    });

    if (res.status === 401) {
        localStorage.removeItem("admin_token");
        window.location.href = "/login";
        throw { status: 401, data: {} };
    }

    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw { status: res.status, data };
    return data;
}

function showAlert(message, type = "success") {
    const box = document.getElementById("alert-box");
    if (!box) return;
    box.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function showError(err) {
    const errors = err?.data?.errors;
    const message = errors
        ? '<ul class="mb-0">' +
          Object.values(errors)
              .flat()
              .map((m) => `<li>${esc(m)}</li>`)
              .join("") +
          "</ul>"
        : esc(
              err?.data?.message ||
                  err?.message ||
                  "Ocurrió un error inesperado.",
          );
    showAlert(message, "danger");
}
