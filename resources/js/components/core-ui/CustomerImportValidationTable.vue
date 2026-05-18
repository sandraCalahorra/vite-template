<template>
    <div v-if="data && Object.keys(data).length" class="flex flex-col gap-6 text-left animate-fade-in">
        <!-- Clientes Válidos -->
        <div v-if="data.valid_customers?.length" class="validation-section">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="size-6 rounded bg-green-300 flex items-center justify-center text-green-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-[11px] font-bold text-gray-700 uppercase tracking-widest">Clientes Válidos</h2>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-tighter">Total: {{ data.valid_customers.length }}</span>
            </div>

            <div class="table-container border border-gray-200 rounded-md overflow-x-auto overflow-y-auto max-h-[300px] bg-white shadow-sm custom-scrollbar-admin">
                <table class="w-full text-left border-collapse table-auto min-w-max">
                    <thead>
                        <tr class="bg-primary/[0.02] border-b-2 border-primary/10">
                            <th class="px-3 py-2 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-primary/5 last:border-r-0 min-w-[100px]">NIF</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-primary/5 last:border-r-0 min-w-[150px]">Nombre</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-primary/5 last:border-r-0 min-w-[120px]">ADA</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-primary/5 last:border-r-0 min-w-[60px]">ID</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-primary/5 last:border-r-0 min-w-[200px]">Proyectos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="customer in data.valid_customers" :key="'valid-' + customer.id" class="hover:bg-green-50/20 transition-colors">
                            <td class="px-3 py-2 text-xs text-gray-600 border-r border-gray-50 last:border-r-0 truncate">{{ customer?.nif || '-' }}</td>
                            <td class="px-3 py-2 text-xs text-gray-600 border-r border-gray-50 last:border-r-0 truncate" :title="customer?.name">{{ customer?.name || '-' }}</td>
                            <td class="px-3 py-2 text-xs text-gray-600 border-r border-gray-50 last:border-r-0 truncate">{{ customer?.projectData?.ada_name || '-' }}</td>
                            <td class="px-3 py-2 text-xs border-r border-gray-50 last:border-r-0">
                                <a :href="`/beneficiarios/${customer?.id}`" target="_blank" class="text-blue-600 font-bold hover:underline">{{ customer?.id }}</a>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-600 truncate border-r border-gray-50 last:border-r-0">
                                <template v-for="(node, idx) in formatProjectTypesWithLinks(getProjectsByCustomerId(customer.id))" :key="idx">
                                    <component :is="node" />
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Clientes no válidos -->
        <div v-if="data.invalid_customers?.length" class="validation-section">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="size-6 rounded bg-red-100 flex items-center justify-center text-red-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h2 class="text-[11px] font-bold text-gray-700 uppercase tracking-widest">Clientes no válidos</h2>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Total: {{ data.invalid_customers.length }}</span>
            </div>

            <div class="table-container border border-red-200 rounded-md overflow-x-auto overflow-y-auto max-h-[300px] bg-white shadow-sm custom-scrollbar-admin">
                <table class="w-full text-left border-collapse table-auto min-w-max">
                    <thead>
                        <tr class="bg-red-50/50 border-b border-red-100">
                            <th class="px-3 py-2 text-[10px] font-bold text-red-800 uppercase tracking-widest border-r border-red-100 last:border-r-0 min-w-[100px]">NIF</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-red-800 uppercase tracking-widest border-r border-red-100 last:border-r-0 min-w-[150px]">Nombre</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-red-800 uppercase tracking-widest border-r border-red-100 last:border-r-0 min-w-[120px]">Dato Erróneo</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-red-800 uppercase tracking-widest border-r border-red-100 last:border-r-0 min-w-[200px]">Error Detectado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-50">
                        <tr v-for="entry in data.invalid_customers" :key="'invalid-' + (entry.customer?.id || entry.customer?.nif || entry.error)" class="hover:bg-red-50/20 transition-colors">
                            <td class="px-3 py-2 text-xs text-gray-600 border-r border-red-50 last:border-r-0 truncate">{{ entry.customer?.nif || '-' }}</td>
                            <td class="px-3 py-2 text-xs text-gray-600 border-r border-red-50 last:border-r-0 truncate">{{ entry.customer?.name || '-' }}</td>
                            <td class="px-3 py-2 text-xs font-bold text-red-700 border-r border-red-50 last:border-r-0 truncate bg-red-50/30">{{ getErrorFieldData(entry) || '-' }}</td>
                            <td class="px-3 py-2 text-xs text-red-800 border-r border-red-50 last:border-r-0 italic">{{ formatImportError(entry.error) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import { defineComponent, h } from "vue";

    export default defineComponent({
        name: "CustomerImportValidationTable",

        props: {
            data: Object,
        },

        methods: {
            formatImportError(error) {
                const translations = {
                    "Missing NIF field in customer data.": "El NIF es obligatorio.",
                    "Invalid NIF value.": "El NIF no es válido.",
                    "Missing name field in customer data.": "El nombre es obligatorio.",
                    "Missing ada field in customer data.": "El ADA es obligatorio.",
                    "Invalid value in ada field of customer data.": "El ADA no es válido.",
                    "Missing ada data in the database.": "El ADA no existe.",
                    "The customer data is valid but the NIF already exists.":
                    "El beneficiario es correcto pero el NIF ya existe.",
                    "Province is not a valid province.": "La provincia no es válida.",
                    "Postal code is not a valid postal code.": "El código postal no es válido.",
                    "Email field is not a valid email address.":
                    "El correo electrónico no es válido.",
                    "Email2 field is not a valid email address.":
                    "El correo electrónico 2 no es válido.",
                    "Mobile phone length or format is invalid.":
                    "La longitud del teléfono no es válida.",
                    "Additional phone length or format is invalid.":
                    "El teléfono 2 no es válido.",
                    "Missing device data in the database.": "El equipo no es válido.",
                    "Voucher must follow the format 1234/A123/12345678.":
                    "El número de bono debe seguir el formato 1234/A123/12345678.",
                    "Voucher_granting_date is not a valid voucher_granting_date.":
                    "La fecha de concesión del bono no tiene un formato válido.",
                    "Invalid value in delivery_province field of customer data.":
                    "La provincia de entrega no es válida.",
                    "Salesperson_phone length or format is invalid.":
                    "El teléfono del comercial no es válido.",
                    "Salesperson_email field is not a valid email address.":
                    "El correo electrónico del comercial no es válido.",
                    "Invalid value in delivery_postal_code field of customer data.":
                    "El código postal de entrega no es válido.",
                    "Delivery_phone_contact length or format is invalid.":
                    "El teléfono de contacto(entrega) no es válido.",
                };

                const fieldTranslations = {
                    phone: "teléfono",
                    phone2: "teléfono 2",
                    delivery_phone_contact: "teléfono de contacto (entrega)",
                    email: "correo electrónico",
                    email2: "correo electrónico 2",
                    voucher: "bono",
                    voucher_granting_date: "fecha de bono",
                    delivery_postal_code: "código postal de entrega",
                    delivery_province: "provincia de entrega",
                    province: "provincia",
                    name: "nombre",
                    nif: "NIF",
                    ada: "ADA",
                    salesperson_phone: "teléfono del comercial",
                    salesperson_email: "correo del comercial",
                    "postal code": "código postal",
                };

                const ruleTranslations = [
                    {
                        pattern: /^not be greater than (\d+) characters?$/i,
                        replace: (n) => `tener más de ${n} caracteres`,
                    },
                    {
                        pattern: /^be (\d+) digits?$/i,
                        replace: (n) => `tener ${n} dígitos`,
                    },
                    {
                        pattern: /^be at least (\d+) characters?$/i,
                        replace: (n) => `tener menos de ${n} caracteres`,
                    },
                    {
                        pattern: /^be a string$/i,
                        replace: () => `ser un texto`,
                    },
                ];

                const formatInvalidMatch = error.match(
                    /^The ([\w\s]+) format is invalid\.$/i
                );
                if (formatInvalidMatch) {
                    const field = formatInvalidMatch[1].toLowerCase();
                    return `El formato del ${
                        fieldTranslations[field] || field
                    } no es válido.`;
                }

                const mustMatch = error.match(/^The ([\w\s]+) must (.+)$/i);
                if (mustMatch) {
                    const rawField = mustMatch[1].trim().toLowerCase();
                    let rule = mustMatch[2].trim().replace(/\.$/, "");

                    for (const rt of ruleTranslations) {
                        const match = rule.match(rt.pattern);
                        if (match) {
                            if (rawField === "voucher" && match.input === "be a string") {
                                return `El número de bono debe seguir el formato 1234/A123/12345678.`;
                            }
                            return `El ${
                                fieldTranslations[rawField] || rawField
                            } no puede ${rt.replace(...match.slice(1))}.`;
                        }
                    }

                    return `El ${fieldTranslations[rawField] || rawField} no debe ${rule}.`;
                }

                return translations[error] || error;
            },

            getErrorFieldData(entry) {
                const error = entry.error.toLowerCase();
                const customer = entry.customer || {};

                const fieldPriority = [
                    { keyword: "delivery_province", field: "delivery_province" },
                    { keyword: "delivery_postal_code", field: "delivery_postal_code" },
                    { keyword: "province", field: "province_name" },
                    { keyword: "postal code", field: "postal_code" },
                    { keyword: "phone2", field: "phone2" },
                    { keyword: "phone", field: "phone" },
                    { keyword: "email2", field: "email2" },
                    { keyword: "email", field: "email" },
                    { keyword: "voucher_granting_date", field: "voucher_granting_date" },
                    { keyword: "voucher", field: "voucher" },
                    { keyword: "device", field: "device" },
                    { keyword: "nif", field: "nif" },
                    { keyword: "name", field: "name" },
                    { keyword: "ada", field: "ada_name" },
                    { keyword: "salesperson_phone", field: "salesperson_phone" },
                ];

                const match = entry.error.match(/^([a-zA-Z0-9_ ]+)( field)? .*is invalid\.$/i);
                let rawField = match ? match[1].toLowerCase().trim() : null;

                if (rawField?.endsWith(" length or format")) {
                    rawField = rawField.replace(" length or format", "");
                }

                let field = rawField ? rawField.replace(/\s+/g, "_") : null;

                if (!field || !customer.hasOwnProperty(field)) {
                    for (const item of fieldPriority) {
                        const keywordRegex = new RegExp(`\\b${item.keyword}\\b`, "i");
                        if (keywordRegex.test(error)) {
                            field = item.field;
                            break;
                        }
                    }
                }

                if (!field) return "-";

                const value = customer[field];
                return value === null || value === undefined || value === "" ? "-" : value;
            },

            getProjectsByCustomerId(customerId) {
                if (!this.data?.projects || !Array.isArray(this.data.projects)) {
                    return [];
                }

                const noProjectsMsg = 'Sin proyectos previos';

                if (
                    this.data.projects.length === 1 &&
                    typeof this.data.projects[0] === 'string' &&
                    this.data.projects[0].trim().includes('ningún proyecto')
                ) {
                    return noProjectsMsg;
                }

                const normalizeType = (type) => {
                    if (!type) return null;
                    return type === "PTS" ? "Puesto de Trabajo" : type.trim();
                };

                const projectStrings = this.data.projects.filter(
                    (p) => typeof p === "string" && p.includes(`id ${customerId}`)
                );

                const projects = projectStrings
                    .map((p) => {
                        const typeMatch = p.match(/tipo ([^,]+)/i);
                        const idMatch = p.match(/id de proyecto (\d+)/i);
                        const type = typeMatch ? normalizeType(typeMatch[1]) : '';
                        const id = idMatch ? idMatch[1] : null;
                        return id ? { type, id } : null;
                    })
                    .filter(Boolean);

                const uniqueMap = new Map();
                for (const p of projects) {
                    const key = p.type || 'Sin tipo';
                    if (!uniqueMap.has(key)) {
                        uniqueMap.set(key, p.id);
                    }
                }

                return [...uniqueMap.entries()].map(([type, id]) => ({
                    type,
                    id,
                }));
            },

            formatProjectTypesWithLinks(projects) {
                if (typeof projects === "string") {
                    return [h("span", projects)];
                }

                if (!projects || projects.length === 0) {
                    return [];
                }

                const nodes = [];
                projects.forEach((project, i) => {
                    nodes.push(
                        h(
                            "a",
                            {
                                href: `/proyecto/${project.id}`,
                                target: "_blank",
                                class: "text-blue-600 font-bold hover:underline",
                            },
                            project.type
                        )
                    );

                    if (i < projects.length - 2) {
                        nodes.push(h("span", ", "));
                    } else if (i === projects.length - 2) {
                        nodes.push(h("span", " y "));
                    }
                });

                return nodes;
            },
        },
    });
</script>

<style scoped>
.validation-section {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Scrollbar profesional y discreto */
.custom-scrollbar-admin::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar-admin::-webkit-scrollbar-track {
    background: #f9fafb;
}
.custom-scrollbar-admin::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.custom-scrollbar-admin::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
