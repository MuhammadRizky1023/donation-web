@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Daftar Donasi') }}</div>
                <div class="card-body">
                    <a href="{{ route('donations.create') }}" class="btn btn-primary mb-3">Tambah Donasi</a>
                    <div id="donation-list" class="row"></div>
                    <p id="no-donations-message" class="text-center" >Tidak ada donasi saat ini.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const db = firebase.database().ref('donations');

    db.on('value', (snapshot) => {
        const donations = snapshot.val();
        const donationList = document.getElementById('donation-list');
        const noDonationsMessage = document.getElementById('no-donations-message');

        if (!donations) {
            noDonationsMessage.style.display = 'block';
        } else {
            noDonationsMessage.style.display = 'none';
            donationList.innerHTML = '';

            Object.keys(donations).forEach((key) => {
                const donation = donations[key];
                const card = document.createElement('div');
                card.className = 'col-md-4';
                card.innerHTML = `
                    <div class="card mb-4">
                        <img src="${donation.image}" class="card-img-top" alt="Gambar Donasi">
                        <div class="card-body">
                            <h5 class="card-title">${donation.name}</h5>
                            <p class="card-text">Rp${donation.amount.toLocaleString()}</p>
                            <a href="{{ url('donations/${key}/edit') }}" class="btn btn-primary">Perbarui</a>
                            <button class="btn btn-danger" onclick="deleteDonation('${key}')">Hapus</button>
                        </div>
                    </div>
                `;
                donationList.appendChild(card);
            });
        }
    }, (errorObject) => {
        console.error("Kesalahan membaca donasi: " + errorObject.code);
    });

    function deleteDonation(id) {
        if (confirm("Apakah Anda yakin ingin menghapus donasi ini?")) {
            db.child(id).remove()
                .then(() => {
                    alert("Donasi berhasil dihapus!");
                    // Tidak perlu reload, karena database realtime akan memperbarui tampilan secara otomatis
                })
                .catch((error) => {
                    console.error("Kesalahan menghapus donasi: " + error);
                });
        }
    }
</script>
@endsection
