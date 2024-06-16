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
                    <div id="donation-list" class="row"></div>
                    <p id="no-donations-message" class="text-center" style="display: none;">No donations available</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    const donationsRef = db.collection("donations");

    // Function to create a donation card
    function createDonationCard(id, name, amount, progress, image) {
        const card = document.createElement('div');
        card.classList.add('col-md-6', 'mb-4');
        card.innerHTML = `
            <div class="card">
                <img src="${image}" class="card-img-top" alt="Donation Image">
                <div class="card-body">
                    <h5 class="card-title">${name}</h5>
                    <p class="card-text">Rp${amount.toLocaleString()}</p>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: ${progress}%" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">${progress}%</div>
                    </div>
                    <button class="btn btn-primary mt-3 donate-btn" data-id="${id}" data-amount="${amount}" data-progress="${progress}">Donate</button>
                    <a href="{{ url('donations/${id}/edit') }}" class="btn btn-warning mt-3">Update</a>
                    <button class="btn btn-danger mt-3" onclick="deleteDonation('${id}')">Delete</button>
                </div>
            </div>
        `;
        return card;
    }

    // Fetch donations from Firestore and display them
    donationsRef.orderBy("created_at", "desc").get().then((querySnapshot) => {
        const donationList = document.getElementById('donation-list');
        const noDonationsMessage = document.getElementById('no-donations-message');

        if (querySnapshot.empty) {
            noDonationsMessage.style.display = 'block';
        } else {
            querySnapshot.forEach((doc) => {
                const donation = doc.data();
                const card = createDonationCard(doc.id, donation.name, donation.amount, donation.progress || 0, donation.image || '/path/to/default/image.jpg');
                donationList.appendChild(card);
            });

            // Add event listeners to all donate buttons
            document.querySelectorAll('.donate-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const donationId = this.dataset.id;
                    const amount = parseInt(this.dataset.amount);
                    fetch('/donations/token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ donationId, amount })
                    })
                    .then(response => response.json())
                    .then(data => {
                        snap.pay(data.token, {
                            onSuccess: function(result) {
                                updateProgress(donationId, amount);
                            },
                            onPending: function(result) {
                                console.log('Pending: ', result);
                            },
                            onError: function(result) {
                                console.log('Error: ', result);
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching token:', error);
                    });
                });
            });
        }
    }).catch((error) => {
        console.error("Error getting donations: ", error);
    });

    function updateProgress(donationId, amount) {
        const donationRef = donationsRef.doc(donationId);

        donationRef.get().then((doc) => {
            if (doc.exists) {
                const donation = doc.data();
                let progress = donation.progress || 0;

                // Increase progress by 10% on each donation
                progress = Math.min(progress + 10, 100);

                donationRef.update({
                    progress: progress
                }).then(() => {
                    const button = document.querySelector(`.donate-btn[data-id="${donationId}"]`);
                    button.dataset.progress = progress;
                    const progressBar = button.previousElementSibling.firstElementChild;
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressBar.textContent = progress + '%';
                }).catch((error) => {
                    console.error("Error updating progress: ", error);
                });
            } else {
                console.log("No such document!");
            }
        }).catch((error) => {
            console.error("Error getting document:", error);
        });
    }

    function deleteDonation(id) {
        if (confirm("Apakah Anda yakin ingin menghapus donasi ini?")) {
            donationsRef.doc(id).delete().then(() => {
                alert("Donasi berhasil dihapus!");
                location.reload();
            }).catch((error) => {
                console.error("Error removing document: ", error);
            });
        }
    }
</script>
@endsection
