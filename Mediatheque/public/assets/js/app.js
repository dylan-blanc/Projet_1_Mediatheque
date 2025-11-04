/**
 * Fichier JavaScript principal de l'application
 */

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", function () {
  // Gestion des messages flash avec auto-hide
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    // Auto-hide après 5 secondes
    setTimeout(function () {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.remove();
      }, 300);
    }, 5000);

    // Permettre de fermer manuellement
    alert.addEventListener("click", function () {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.remove();
      }, 300);
    });
  });

  // Validation de formulaire côté client
  const forms = document.querySelectorAll("form");
  forms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      // Ne pas valider les formulaires avec upload de fichiers (multipart/form-data)
      if (form.enctype === 'multipart/form-data') {
        return true; // Laisser passer sans validation
      }
      
      if (!validateForm(form)) {
        e.preventDefault();
      }
    });
  });

  // // Amélioration UX pour les boutons de soumission
  // const submitButtons = document.querySelectorAll('button[type="submit"]');
  // submitButtons.forEach(function(button) {
  //     button.addEventListener('click', function() {
  //         const form = button.closest('form');
  //         if (form && validateForm(form)) {
  //             button.disabled = true;
  //             button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

  //             // Réactiver après 5 secondes en cas de problème
  //             setTimeout(function() {
  //                 button.disabled = false;
  //                 button.innerHTML = button.getAttribute('data-original-text') || 'Envoyer';
  //             }, 5000);
  //         }
  //     });
  // });

  // Smooth scroll pour les ancres
  const anchors = document.querySelectorAll('a[href^="#"]');
  anchors.forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Confirmation pour les actions de suppression
  const deleteLinks = document.querySelectorAll(
    'a[href*="delete"], button[data-action="delete"]'
  );
  deleteLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer cet élément ?")) {
        e.preventDefault();
      }
    });
  });

  // Animation d'entrée pour les cartes
  const cards = document.querySelectorAll(".feature-card, .step, .info-box");
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  cards.forEach(function (card) {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(card);
  });
});

/**
 * Valide un formulaire
 */
function validateForm(form) {
  let isValid = true;

  // Validation des champs requis (ignorer les champs dans les sections cachées)
  const requiredFields = form.querySelectorAll("[required]");
  requiredFields.forEach(function (field) {
    // Ignorer les champs dans les sections cachées (display: none)
    const parentSection = field.closest('.type-fields, [style*="display: none"], [style*="display:none"]');
    const isHidden = parentSection && (
      parentSection.style.display === 'none' || 
      window.getComputedStyle(parentSection).display === 'none'
    );
    
    if (isHidden) {
      return; // Sauter ce champ
    }
    
    if (!field.value.trim()) {
      showFieldError(field, "Ce champ est obligatoire");
      isValid = false;
    } else {
      hideFieldError(field);
    }
  });

  // Validation des emails
  const emailFields = form.querySelectorAll('input[type="email"]');
  emailFields.forEach(function (field) {
    if (field.value && !isValidEmail(field.value)) {
      showFieldError(field, "Adresse email invalide");
      isValid = false;
    }
  });

  // Validation des mots de passe
  const passwordField = form.querySelector('input[name="password"]');
  const confirmPasswordField = form.querySelector(
    'input[name="confirm_password"]'
  );

  if (passwordField && passwordField.value.length < 6) {
    showFieldError(
      passwordField,
      "Le mot de passe doit contenir au moins 6 caractères"
    );
    isValid = false;
  }

  if (
    confirmPasswordField &&
    passwordField &&
    confirmPasswordField.value !== passwordField.value
  ) {
    showFieldError(
      confirmPasswordField,
      "Les mots de passe ne correspondent pas"
    );
    isValid = false;
  }

  return isValid;
}

/**
 * Affiche une erreur sur un champ
 */
function showFieldError(field, message) {
  hideFieldError(field);

  const error = document.createElement("div");
  error.className = "field-error";
  error.textContent = message;
  error.style.color = "#ef4444";
  error.style.fontSize = "0.875rem";
  error.style.marginTop = "0.25rem";

  field.style.borderColor = "#ef4444";
  field.parentNode.appendChild(error);
}

/**
 * Cache l'erreur d'un champ
 */
function hideFieldError(field) {
  const existingError = field.parentNode.querySelector(".field-error");
  if (existingError) {
    existingError.remove();
  }
  field.style.borderColor = "";
}

/**
 * Valide une adresse email
 */
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Affiche un message de notification
 */
function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `alert alert-${type}`;
  notification.textContent = message;
  notification.style.position = "fixed";
  notification.style.top = "20px";
  notification.style.right = "20px";
  notification.style.zIndex = "1000";
  notification.style.minWidth = "300px";
  notification.style.cursor = "pointer";

  document.body.appendChild(notification);

  // Auto-hide
  setTimeout(function () {
    notification.style.opacity = "0";
    setTimeout(function () {
      notification.remove();
    }, 300);
  }, 5000);

  // Click to hide
  notification.addEventListener("click", function () {
    notification.remove();
  });
}

/**
 * Utilitaire AJAX simple
 */
function ajax(url, options = {}) {
  const defaults = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  };

  const config = Object.assign({}, defaults, options);

  return fetch(url, config)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur réseau");
      }
      return response.json();
    })
    .catch((error) => {
      console.error("Erreur AJAX:", error);
      showNotification("Une erreur est survenue", "error");
      throw error;
    });
}
// ------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------ORIGINAL PAS TOUCHER-------------------------------------------------
// ----------------------------------------------------------EN HAUT-------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  // Gestion des messages flash (clic pour fermer)
  const alerts = document.querySelectorAll(".alert, .flash-message");
  alerts.forEach(function (alert) {
    alert.addEventListener("click", function () {
      alert.style.display = "none";
    });
  });

  // Confirmation pour les actions destructives (suppression, etc.)
  const deleteButtons = document.querySelectorAll("[data-confirm]");
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function (e) {
      const message = button.getAttribute("data-confirm") || "Êtes-vous sûr ?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // Auto-masquage des messages flash après 5 secondes
  setTimeout(function () {
    const autoHideAlerts = document.querySelectorAll(
      ".alert.auto-hide, .flash-message.auto-hide"
    );
    autoHideAlerts.forEach(function (alert) {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.style.display = "none";
      }, 300);
    });
  }, 5000);
});

/**
 * Capitalisation automatique pendant la saisie
 * Gère les noms composés (espaces) et prénoms composés (tirets)
 * Conforme CDC 3.1.1
 * 
 * @param {HTMLInputElement} input - Champ input à traiter
 */
function capitalizeFirstLetter(input) {
    let value = input.value;
    
    // Récupérer la position du curseur avant modification
    let cursorPos = input.selectionStart;
    
    // Transformer le texte
    let formatted = value
        .toLowerCase() // Tout en minuscules d'abord
        .split(' ')    // Découper par espaces
        .map(part => {
            // Pour chaque partie séparée par un espace
            return part
                .split('-') // Découper par tirets
                .map(subpart => {
                    // Capitaliser chaque sous-partie
                    if (subpart.length > 0) {
                        return subpart.charAt(0).toUpperCase() + subpart.slice(1);
                    }
                    return subpart;
                })
                .join('-'); // Rejoindre avec les tirets
        })
        .join(' '); // Rejoindre avec les espaces
    
    // Appliquer la nouvelle valeur
    input.value = formatted;
    
    // Restaurer la position du curseur
    input.setSelectionRange(cursorPos, cursorPos);
}

/**
 * -------------------------------------------------------------------------
 * Scripts spécifiques aux vues (déplacés depuis les views)
 * -------------------------------------------------------------------------
 */

/**
 * Utilisé dans:
 * - views/admin/users.php
 * - views/admin/user_detail.php (bouton "Envoyer un email")
 */
function sendEmail(email, name) {
  window.location.href =
    "mailto:" + email + "?subject=Médiathèque TLN - Message pour " + name;
}

/**
 * Utilisé dans: views/admin/user_detail.php (bouton "Rappel retards")
 */
function sendReminderEmail(email, name, retardCount) {
  const subject = "Médiathèque TLN - Rappel de retour de médias";
  const body =
    "Bonjour " +
    name +
    ",\n\nVous avez " +
    retardCount +
    " emprunt(s) en retard. Merci de bien vouloir retourner vos médias dans les plus brefs délais.\n\nCordialement,\nL'équipe de la Médiathèque TLN";
  window.location.href =
    "mailto:" +
    email +
    "?subject=" +
    encodeURIComponent(subject) +
    "&body=" +
    encodeURIComponent(body);
}

/**
 * Utilisé dans: views/admin/user_detail.php (bouton "Imprimer le rapport")
 */
function printUserReport(userId) {
  window.print();
}

/**
 * Utilisé dans: views/admin/user_detail.php (filtre de l'historique)
 */
function filterHistory(status) {
  const rows = document.querySelectorAll(".history-row");
  rows.forEach((row) => {
    if (status === "all") {
      row.style.display = "";
    } else if (status === "returned") {
      // Afficher uniquement les emprunts rendus (statut = "rendu")
      row.style.display = row.dataset.status === "rendu" ? "" : "none";
    } else if (status === "overdue") {
      // Afficher uniquement ceux qui ont été/sont en retard (data-overdue="1")
      row.style.display = row.dataset.overdue === "1" ? "" : "none";
    }
  });
}

/**
 * Vues:
 * - views/media/add.php
 * - views/media/edit.php
 * Fonctionnalités: champs conditionnels selon le type + options de genre (depuis DB)
 */
(function () {
  // Récupérer les genres depuis les data attributes (DB)
  function getGenresData() {
    const addDataEl = document.getElementById('genre-data-add');
    const editDataEl = document.getElementById('genre-data-edit');
    
    if (addDataEl) {
      return {
        genres: JSON.parse(addDataEl.dataset.genres),
        oldType: addDataEl.dataset.oldType || '',
        oldGenre: addDataEl.dataset.oldGenre || ''
      };
    } else if (editDataEl) {
      return {
        genres: JSON.parse(editDataEl.dataset.genres),
        currentType: editDataEl.dataset.currentType || '',
        currentGenre: editDataEl.dataset.currentGenre || ''
      };
    }
    return null;
  }

  function updateGenreOptions(type) {
    const genreSelect = document.getElementById("genre");
    if (!genreSelect) return;

    const data = getGenresData();
    if (!data) return;

    const previousValue = genreSelect.value || "";

    // Réinitialise les options
    genreSelect.innerHTML = '<option value="">Sélectionner un genre</option>';

    const list = data.genres[type] || [];
    list.forEach((genre) => {
      const option = document.createElement("option");
      option.value = genre;
      option.textContent = genre;
      genreSelect.appendChild(option);
    });

    // Logique de sélection:
    // 1) Page edit: sélectionner le genre actuel du média
    // 2) Page add: si old() existe (erreur de validation), le resélectionner
    // 3) Sinon: conserver la sélection précédente si compatible
    if (data.currentGenre && list.includes(data.currentGenre)) {
      genreSelect.value = data.currentGenre;
    } else if (data.oldGenre && list.includes(data.oldGenre)) {
      genreSelect.value = data.oldGenre;
    } else if (previousValue && list.includes(previousValue)) {
      genreSelect.value = previousValue;
    }
  }

  function showTypeFields(type) {
    // Masquer tous les blocs spécifiques
    document.querySelectorAll(".type-fields").forEach((el) => {
      el.style.display = "none";
    });

    // Afficher le bloc correspondant
    if (type) {
      const fields = document.getElementById(type + "-fields");
      if (fields) fields.style.display = "block";
    }

    // Mettre à jour les genres depuis la DB
    updateGenreOptions(type);
  }

  // Exposer la fonction pour l'attribut onchange du HTML
  window.showTypeFields = showTypeFields;

  // Initialisation
  document.addEventListener("DOMContentLoaded", function () {
    const typeSelect = document.getElementById("type");
    if (!typeSelect) return;

    const data = getGenresData();
    if (!data) return;

    // Page edit: initialiser avec le type actuel
    if (data.currentType && typeSelect.value) {
      showTypeFields(typeSelect.value);
    }
    // Page add: initialiser avec old() si erreur de validation
    else if (data.oldType) {
      typeSelect.value = data.oldType;
      showTypeFields(data.oldType);
    }
    // Sinon: si un type est déjà sélectionné, l'afficher
    else if (typeSelect.value) {
      showTypeFields(typeSelect.value);
    }

    typeSelect.addEventListener("change", function () {
      showTypeFields(this.value);
    });
  });
})();

/**
 * Carousel Top Médias - Navigation avec icônes dynamiques
 */
document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".carousel-slide");
  const prevBtn = document.querySelector(".nav-btn.prev");
  const nextBtn = document.querySelector(".nav-btn.next");

  // Vérifier que les éléments existent
  if (!slides.length || !prevBtn || !nextBtn) {
    console.log("Carousel: éléments non trouvés");
    return;
  }

  console.log("Carousel initialisé avec", slides.length, "slides");

  let current = 0;
  const icons = { livre: "fa-book", film: "fa-film", jeu: "fa-gamepad" };

  function updateNav() {
    const activeSlide = slides[current];
    const prevType = activeSlide.dataset.prev;
    const nextType = activeSlide.dataset.next;

    if (prevBtn && prevBtn.querySelector("i")) {
      prevBtn.querySelector("i").className = `fas ${icons[prevType]}`;
    }
    if (nextBtn && nextBtn.querySelector("i")) {
      nextBtn.querySelector("i").className = `fas ${icons[nextType]}`;
    }
  }

  function show(n) {
    slides[current].classList.remove("active");
    current = (n + slides.length) % slides.length;
    slides[current].classList.add("active");
    updateNav();
  }

  prevBtn.onclick = () => {
    console.log("Prev clicked");
    show(current - 1);
  };

  nextBtn.onclick = () => {
    console.log("Next clicked");
    show(current + 1);
  };

  updateNav();
});

/* ================================================================
   GESTION DES GENRES - Formulaires Add/Edit
   ================================================================ */

document.addEventListener('DOMContentLoaded', function() {
  
  // ===== FORMULAIRE D'AJOUT =====
  const addTypeSelect = document.getElementById('type');
  const addGenresContainer = document.getElementById('genres-checkboxes');
  const addGenresValidation = document.getElementById('genres-validation');
  const addGenreData = document.getElementById('genre-data-add');
  
  if (addTypeSelect && addGenresContainer && addGenreData && addGenresValidation) {
    let allGenres = [];
    let allGenreIds = [];
    let oldGenreIds = [];
    
    try {
      allGenres = JSON.parse(addGenreData.dataset.genres || '[]');
      allGenreIds = JSON.parse(addGenreData.dataset.genreIds || '[]');
      oldGenreIds = JSON.parse(addGenreData.dataset.oldGenreIds || '[]');
    } catch(e) {
      addGenresContainer.innerHTML = '<p class="text-danger">Erreur de chargement des genres</p>';
      return;
    }
    
    function displayAddGenres() {
      if (!addTypeSelect.value) {
        addGenresContainer.innerHTML = '<p class="text-muted">Veuillez d\'abord sélectionner un type de média</p>';
        return;
      }
      
      if (allGenres.length === 0) {
        addGenresContainer.innerHTML = '<p class="text-danger">Aucun genre disponible</p>';
        return;
      }
      
      addGenresContainer.innerHTML = '';
      
      allGenres.forEach((name, idx) => {
        const genreId = parseInt(allGenreIds[idx]);
        const isChecked = oldGenreIds.includes(genreId) || oldGenreIds.includes(String(genreId));
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = '<label class="form-check-label"><input type="checkbox" name="genre_ids[]" value="' + genreId + '" class="form-check-input genre-checkbox"' + (isChecked ? ' checked' : '') + '> ' + name + '</label>';
        addGenresContainer.appendChild(div);
      });
      
      addGenresContainer.querySelectorAll('.genre-checkbox').forEach(function(cb) {
        cb.addEventListener('change', validateAddGenres);
      });
      
      console.log('✅ Checkboxes affichées:', allGenres.length);
    }
    
    function validateAddGenres() {
      const checked = addGenresContainer.querySelectorAll('.genre-checkbox:checked');
      const count = checked.length;
      let error = addGenresContainer.querySelector('.genre-error');
      let counter = addGenresContainer.querySelector('.genre-counter');
      
      if (count === 0) {
        addGenresValidation.setCustomValidity('Sélectionnez au moins 1 genre');
        if (!error) {
          error = document.createElement('p');
          error.className = 'genre-error text-danger';
          addGenresContainer.appendChild(error);
        }
        error.textContent = '⚠️ Sélectionnez au moins 1 genre';
      } else if (count > 5) {
        addGenresValidation.setCustomValidity('Maximum 5 genres');
        addGenresContainer.querySelectorAll('.genre-checkbox:not(:checked)').forEach(function(box) {
          box.disabled = true;
        });
        if (!error) {
          error = document.createElement('p');
          error.className = 'genre-error text-danger';
          addGenresContainer.appendChild(error);
        }
        error.textContent = '⚠️ Maximum 5 genres (' + count + ' sélectionnés)';
      } else {
        addGenresValidation.setCustomValidity('');
        addGenresContainer.querySelectorAll('.genre-checkbox').forEach(function(box) {
          box.disabled = false;
        });
        if (error) error.remove();
      }
      
      if (!counter) {
        counter = document.createElement('p');
        counter.className = 'genre-counter text-muted';
        addGenresContainer.appendChild(counter);
      }
      counter.textContent = '✓ ' + count + ' genre(s) (max 5)';
    }
    
    addTypeSelect.addEventListener('change', displayAddGenres);
    if (addTypeSelect.value) displayAddGenres();
  }
  
  // ===== FORMULAIRE D'ÉDITION =====
  const editGenresContainer = document.getElementById('genres-checkboxes-edit');
  const editGenresValidation = document.getElementById('genres-validation-edit');
  const editGenreData = document.getElementById('genre-data-edit');
  
  if (editGenresContainer && editGenreData && editGenresValidation) {
    console.log('✅ Formulaire d\'édition détecté');
    
    let allGenres = [];
    let allGenreIds = [];
    let currentIds = [];
    
    try {
      allGenres = JSON.parse(editGenreData.dataset.genres || '[]');
      allGenreIds = JSON.parse(editGenreData.dataset.genreIds || '[]');
      currentIds = JSON.parse(editGenreData.dataset.currentGenreIds || '[]');
      console.log('📦 Genres chargés:', allGenres.length, '| Actuels:', currentIds);
    } catch(e) {
      console.error('❌ Erreur parsing genres edit:', e);
      editGenresContainer.innerHTML = '<p class="text-danger">Erreur chargement genres</p>';
    }
    
    function displayEditGenres() {
      if (allGenres.length === 0) {
        editGenresContainer.innerHTML = '<p class="text-danger">Aucun genre disponible</p>';
        return;
      }
      
      editGenresContainer.innerHTML = '';
      
      allGenres.forEach((name, idx) => {
        const id = parseInt(allGenreIds[idx]);
        const checked = currentIds.includes(id);
        
        const div = document.createElement('div');
        div.className = 'genre-checkbox-item';
        div.innerHTML = '<label><input type="checkbox" name="genre_ids[]" value="' + id + '" class="genre-checkbox" ' + (checked ? 'checked' : '') + '><span>' + name + '</span></label>';
        editGenresContainer.appendChild(div);
      });
      
      editGenresContainer.querySelectorAll('.genre-checkbox').forEach(function(cb) {
        cb.addEventListener('change', validateEditGenres);
      });
      
      validateEditGenres();
      console.log('✅ Checkboxes affichées:', allGenres.length);
    }
    
    function validateEditGenres() {
      const checked = editGenresContainer.querySelectorAll('.genre-checkbox:checked');
      const count = checked.length;
      let error = editGenresContainer.querySelector('.genre-error');
      let counter = editGenresContainer.querySelector('.genre-counter');
      
      if (count === 0) {
        editGenresValidation.setCustomValidity('Sélectionnez au moins 1 genre');
        if (!error) {
          error = document.createElement('p');
          error.className = 'genre-error text-danger';
          editGenresContainer.appendChild(error);
        }
        error.textContent = '⚠️ Sélectionnez au moins 1 genre';
      } else if (count > 5) {
        editGenresValidation.setCustomValidity('Maximum 5 genres');
        editGenresContainer.querySelectorAll('.genre-checkbox:not(:checked)').forEach(function(box) {
          box.disabled = true;
        });
        if (!error) {
          error = document.createElement('p');
          error.className = 'genre-error text-danger';
          editGenresContainer.appendChild(error);
        }
        error.textContent = '⚠️ Maximum 5 genres (' + count + ')';
      } else {
        editGenresValidation.setCustomValidity('');
        editGenresContainer.querySelectorAll('.genre-checkbox').forEach(function(box) {
          box.disabled = false;
        });
        if (error) error.remove();
      }
      
      if (!counter) {
        counter = document.createElement('p');
        counter.className = 'genre-counter text-muted';
        editGenresContainer.appendChild(counter);
      }
      counter.textContent = '✓ ' + count + ' genre(s) (max 5)';
    }
    
    displayEditGenres();
  }
  
});

/**
 * ========================================================
 * MENU BURGER MOBILE - Navigation responsive
 * ========================================================
 */
document.addEventListener('DOMContentLoaded', function() {
  const burgerMenu = document.getElementById('burger-menu');
  const navMenu = document.getElementById('nav-menu');
  const body = document.body;
  
  if (burgerMenu && navMenu) {
    // Toggle du menu au clic sur le burger
    burgerMenu.addEventListener('click', function(e) {
      e.stopPropagation();
      burgerMenu.classList.toggle('active');
      navMenu.classList.toggle('active');
      body.classList.toggle('menu-open');
    });
    
    // Fermer le menu au clic en dehors
    document.addEventListener('click', function(e) {
      if (navMenu.classList.contains('active') && 
          !navMenu.contains(e.target) && 
          !burgerMenu.contains(e.target)) {
        burgerMenu.classList.remove('active');
        navMenu.classList.remove('active');
        body.classList.remove('menu-open');
      }
    });
    
    // Gestion des sous-menus (dropdowns) - fonctionne en mobile ET desktop
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
    dropdownToggles.forEach(function(toggle) {
      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const dropdown = toggle.nextElementSibling;
        if (dropdown && dropdown.classList.contains('nav-dropdown-menu')) {
          // Sur mobile - gestion spécifique
          if (window.innerWidth <= 768) {
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.nav-dropdown-menu').forEach(function(menu) {
              if (menu !== dropdown && menu.classList.contains('active')) {
                menu.classList.remove('active');
                const otherChevron = menu.previousElementSibling.querySelector('.fa-chevron-down');
                if (otherChevron) {
                  otherChevron.style.transform = 'rotate(0deg)';
                }
              }
            });
            
            // Toggle le dropdown actuel
            const isActive = dropdown.classList.contains('active');
            dropdown.classList.toggle('active');
            
            // Rotation de l'icône chevron
            const chevron = toggle.querySelector('.fa-chevron-down');
            if (chevron) {
              chevron.style.transform = !isActive ? 'rotate(180deg)' : 'rotate(0deg)';
            }
          }
        }
      });
    });
    
    // Fermer le menu mobile lors du clic sur un lien (incluant ceux dans les dropdowns)
    const navLinks = navMenu.querySelectorAll('a:not(.nav-dropdown-toggle)');
    navLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
          // Permettre la navigation en laissant le lien fonctionner normalement
          // Fermer le menu après un petit délai pour permettre la navigation
          setTimeout(function() {
            burgerMenu.classList.remove('active');
            navMenu.classList.remove('active');
            body.classList.remove('menu-open');
          }, 100);
        }
      });
    });
    
    // Fermer le menu lors du redimensionnement de la fenêtre (retour desktop)
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        burgerMenu.classList.remove('active');
        navMenu.classList.remove('active');
        body.classList.remove('menu-open');
        
        // Réinitialiser les dropdowns
        document.querySelectorAll('.nav-dropdown-menu').forEach(function(menu) {
          menu.classList.remove('active');
        });
      }
    });
  }
});

