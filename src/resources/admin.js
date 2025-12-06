/*
  Requirement: Make the "Manage Resources" page interactive.
*/

// --- Global Data Store ---
let resources = [];

// --- Element Selections ---
const resourceForm = document.querySelector('#resource-form');
const resourcesTableBody = document.querySelector('#resources-tbody');

// --- Functions ---

/**
 * Creates a table row for a resource
 */
function createResourceRow(resource) {
  const tr = document.createElement('tr');

  // Title TD
  const titleTd = document.createElement('td');
  titleTd.textContent = resource.title;
  tr.appendChild(titleTd);

  // Description TD
  const descTd = document.createElement('td');
  descTd.textContent = resource.description;
  tr.appendChild(descTd);

  // Actions TD
  const actionsTd = document.createElement('td');

  // Edit Button
  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-btn');
  editBtn.dataset.id = resource.id;
  actionsTd.appendChild(editBtn);

  // Delete Button
  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = resource.id;
  actionsTd.appendChild(deleteBtn);

  tr.appendChild(actionsTd);

  return tr;
}

/**
 * Renders the resources table
 */
function renderTable() {
  resourcesTableBody.innerHTML = '';
  resources.forEach(resource => {
    const tr = createResourceRow(resource);
    resourcesTableBody.appendChild(tr);
  });
}

/**
 * Handles form submission to add a new resource
 */
function handleAddResource(event) {
  event.preventDefault();

  const titleInput = document.querySelector('#resource-title');
  const descInput = document.querySelector('#resource-description');
  const linkInput = document.querySelector('#resource-link');

  const newResource = {
    id: `res_${Date.now()}`,
    title: titleInput.value,
    description: descInput.value,
    link: linkInput.value
  };

  resources.push(newResource);
  renderTable();
  resourceForm.reset();
}

/**
 * Handles clicks on the table (delete/edit delegation)
 */
function handleTableClick(event) {
  const target = event.target;

  if (target.classList.contains('delete-btn')) {
    const idToDelete = target.dataset.id;
    resources = resources.filter(r => r.id !== idToDelete);
    renderTable();
  }

  // Optional: Add Edit functionality later
  if (target.classList.contains('edit-btn')) {
    const idToEdit = target.dataset.id;
    const resource = resources.find(r => r.id === idToEdit);
    if (resource) {
      document.querySelector('#resource-title').value = resource.title;
      document.querySelector('#resource-description').value = resource.description;
      document.querySelector('#resource-link').value = resource.link;

      // Delete the original resource so we can re-add it on submit
      resources = resources.filter(r => r.id !== idToEdit);
      renderTable();
    }
  }
}

/**
 * Loads resources from JSON and initializes page
 */
async function loadAndInitialize() {
  try {
    const response = await fetch('resources.json');
    if (!response.ok) throw new Error('Failed to load resources.json');
    resources = await response.json();
    renderTable();
  } catch (error) {
    console.error(error);
    resources = []; // fallback to empty array
  }

  // Event listeners
  resourceForm.addEventListener('submit', handleAddResource);
  resourcesTableBody.addEventListener('click', handleTableClick);
}

// --- Initial Page Load ---
loadAndInitialize();
