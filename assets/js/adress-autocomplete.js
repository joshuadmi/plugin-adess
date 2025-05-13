(function () {
    const streetInput = document.getElementById("profile_street");
    const list = document.getElementById("autocomplete-list");
    const postalInput = document.getElementById("profile_postal_code");
    const cityInput = document.getElementById("profile_city");
  
    let controller;
    if (streetInput) {
      streetInput.addEventListener("input", async function () {
        const query = this.value.trim();
        if (query.length < 3) {
          list.innerHTML = "";
          return;
        }
        if (controller) controller.abort();
        controller = new AbortController();
  
        try {
          const resp = await fetch(
            "https://api-adresse.data.gouv.fr/search/?q=" +
              encodeURIComponent(query),
            { signal: controller.signal }
          );
          const { features } = await resp.json();
  
          list.innerHTML = features
            .map((f) => {
              const p = f.properties;
              // on reconstruit la "voie" : numéro + nom
              const voie = p.housenumber ? `${p.housenumber} ${p.name}` : p.name;
  
              // <li> affiche le label complet mais stocke la voie seule
              return `
              <li
                data-voie="${voie}"
                data-postcode="${p.postcode}"
                data-city="${p.city}"
              >
                ${p.label}
              </li>
            `;
            })
            .join("");
        } catch (e) {
          console.warn("Adresse API", e);
        }
      });
  
      list.addEventListener("click", function (e) {
        const li = e.target.closest("li");
        if (!li) return;
  
        // on remplit chaque champ avec la donnée appropriée
        streetInput.value = li.dataset.voie.trim();
        postalInput.value = li.dataset.postcode;
        cityInput.value = li.dataset.city;
        list.innerHTML = "";
      });
    }
  })();
  
  function initAddressAutocomplete({ inputId, listId, postalId, cityId }) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    const postal = document.getElementById(postalId);
    const city = document.getElementById(cityId);
    let controller;
  
    if (!input) return;
    
    input.addEventListener("input", async function () {
      const q = this.value.trim();
      if (q.length < 3) {
        list.innerHTML = "";
        return;
      }
      controller?.abort();
      controller = new AbortController();
  
      try {
        const resp = await fetch(
          "https://api-adresse.data.gouv.fr/search/?q=" + encodeURIComponent(q),
          { signal: controller.signal }
        );
        const { features } = await resp.json();
  
        list.innerHTML = features
          .map((f) => {
            const p = f.properties;
            // on construit voie = numéro + nom (sans doublon)
            const voie =
              p.housenumber && !p.name.startsWith(p.housenumber)
                ? `${p.housenumber} ${p.name}`
                : p.name;
  
            return `
              <li
                data-voie="${voie}"
                data-postcode="${p.postcode}"
                data-city="${p.city}"
              >${p.label}</li>
            `;
          })
          .join("");
      } catch (e) {
        console.warn("Adresse API", e);
      }
    });
  
    list.addEventListener("click", function (e) {
      const li = e.target.closest("li");
      if (!li) return;
      input.value = li.dataset.voie.trim();
      postal.value = li.dataset.postcode;
      city.value = li.dataset.city;
      list.innerHTML = "";
    });
  }
  
  // initialisation pour l’adresse principale…
  initAddressAutocomplete({
    inputId: "profile_street",
    listId: "autocomplete-list",
    postalId: "profile_postal_code",
    cityId: "profile_city",
  });
  
  // …et pour l’adresse secondaire
  initAddressAutocomplete({
    inputId: "profile_second_street",
    listId: "autocomplete-list-2",
    postalId: "profile_second_postal_code",
    cityId: "profile_second_city",
  });
  
  // Synchronisation secondaire
  function syncSecondaryAddress() {
    const street = document.getElementById('profile_street').value;
    const postcode = document.getElementById('profile_postal_code').value;
    const city = document.getElementById('profile_city').value;
  
    document.getElementById('profile_second_street').value = street;
    document.getElementById('profile_second_postal_code').value = postcode;
    document.getElementById('profile_second_city').value = city;
  }
  // Gestion de l'affichage
  const sameCb = document.getElementById('sameAddress');
  const secondContainer = document.getElementById('second-address-container');
  
  sameCb.addEventListener('change', () => {
    if (sameCb.checked) {
      secondContainer.style.display = 'none';
      syncSecondaryAddress();
    } else {
      secondContainer.style.display = '';
    }
  });
  
  // Exécution au chargement
  document.addEventListener('DOMContentLoaded', () => {
    if (sameCb.checked) {
      secondContainer.style.display = 'none';
      syncSecondaryAddress();
    }
  });
  