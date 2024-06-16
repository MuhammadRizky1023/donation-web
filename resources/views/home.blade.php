@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Daftar Donasi') }}
                    <a href="{{ route('donations.create') }}" class="btn btn-primary float-right">Tambah Donasi</a>
                </div>
                <div class="card-body">
                    <div id="donation-cards" class="row"></div>
                    <p id="no-donations-message" class="text-center">No donations available</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const donationsRef = db.collection("donations");

    // Function to create a donation card
    function createDonationCard(id, name, amount, progress) {
        const card = document.createElement('div');
        card.classList.add('col-md-6', 'mb-4');
        card.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${name}</h5>
                    <p class="card-text">Rp${amount.toLocaleString()}</p>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: ${progress}%" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">${progress}%</div>
                    </div>
                    <button class="btn btn-primary mt-3 donate-btn" data-id="${id}" data-amount="${amount}" data-progress="${progress}">Donate</button>
                </div>
            </div>
        `;
        return card;
    }

    // Fetch donations from Firestore and display them
    donationsRef.orderBy("created_at", "desc").get().then((querySnapshot) => {
        const donationCards = document.getElementById('donation-cards');
        const noDonationsMessage = document.getElementById('no-donations-message');

        if (querySnapshot.empty) {
            noDonationsMessage.style.display = 'block';
        } else {
            querySnapshot.forEach((doc) => {
                const donation = doc.data();
                const card = createDonationCard(doc.id, donation.name, donation.amount, donation.progress || 0);
                donationCards.appendChild(card);
            });

            // Add event listeners to all donate buttons
            document.querySelectorAll('.donate-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const donationId = this.dataset.id;
                    const amount = parseInt(this.dataset.amount);
                    let progress = parseInt(this.dataset.progress);

                    // Increase progress by 10% on each donation
                    progress = Math.min(progress + 10, 100);

                    // Update Firestore document
                    donationsRef.doc(donationId).update({
                        progress: progress
                    }).then(() => {
                        this.dataset.progress = progress;
                        const progressBar = this.previousElementSibling.firstElementChild;
                        progressBar.style.width = progress + '%';
                        progressBar.setAttribute('aria-valuenow', progress);
                        progressBar.textContent = progress + '%';
                    }).catch((error) => {
                        console.error("Error updating progress: ", error);
                    });
                });
            });
        }
    }).catch((error) => {
        console.error("Error getting donations: ", error);
    });
</script>
@endsection


