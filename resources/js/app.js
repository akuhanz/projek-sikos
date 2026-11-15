// ====== Tombol favorit tanpa reload ======
document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-favorit')) return;

    e.preventDefault();
    const tombol = form.querySelector('button');
    tombol.disabled = true;

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
        });
        if (!res.ok) throw new Error('gagal');

        const { favorit } = await res.json();

        if (!favorit && form.hasAttribute('data-hapus-kartu')) {
            form.closest('[data-kartu]')?.remove();
            return;
        }

        const teks = favorit ? 'Hapus dari favorit' : 'Simpan ke favorit';
        tombol.dataset.aktif = favorit ? 'true' : 'false';
        tombol.setAttribute('aria-pressed', favorit ? 'true' : 'false');
        tombol.setAttribute('aria-label', teks);
        tombol.title = teks;
    } catch (err) {
        form.submit();
    } finally {
        tombol.disabled = false;
    }
});

// ====== Menu dropdown navbar di layar kecil ======
function aturMenu(menu, buka) {
    const panel = menu.querySelector('[data-menu-panel]');
    const tombol = menu.querySelector('[data-menu-tombol]');

    panel.hidden = !buka;
    tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
    tombol.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu');
    tombol.querySelector('[data-ikon-buka]').classList.toggle('hidden', buka);
    tombol.querySelector('[data-ikon-tutup]').classList.toggle('hidden', !buka);
}

document.addEventListener('click', (e) => {
    document.querySelectorAll('[data-menu]').forEach((menu) => {
        const sedangBuka = !menu.querySelector('[data-menu-panel]').hidden;
        const kenaTombol = e.target.closest('[data-menu-tombol]')?.closest('[data-menu]') === menu;

        if (kenaTombol) {
            aturMenu(menu, !sedangBuka);
        } else if (sedangBuka && (!menu.contains(e.target) || e.target.closest('a'))) {
            aturMenu(menu, false);
        }
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[data-menu]').forEach((menu) => aturMenu(menu, false));
    }
});