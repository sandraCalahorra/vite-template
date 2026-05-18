<template>
	<div>
		<div class="flex flex-col gap-3 px-4 sm:px-0">
			<!-- BAR -->
			<div class="flex flex-wrap items-center w-full gap-4">

                <div class="flex flex-col flex-grow gap-2">
                    <!-- ROW 1: Search & Main Buttons -->
                    <div class="flex flex-wrap items-center w-full gap-3">
                        <!-- TEXT SEARCH BAR -->
                        <div
                            v-if="filterOptions.includes('stringSearch')"
                            class="relative flex items-center w-full text-gray-500 md:max-w-80 col-span-full md:w-7/12 lg:w-1/2 xl:w-5/12 focus-within:text-gray-700"
                        >
                            <IconMagnifyingGlass class="absolute max-w-4" />
                            <div class="flex flex-row items-center w-full gap-2">
                                <input
                                    v-model="workingFilters.stringSearch"
                                    type="text"
                                    placeholder="Buscar..."
                                    @keyup="debouncedFilterEmit"
                                    class="w-full h-8 px-3 py-2 pl-8 leading-6 text-gray-700 border border-gray-300 rounded appearance-none form-input focus:outline-none focus:shadow-outline focus:ring-1 focus:ring-primary focus:border-primary"
                                />
                                <div v-if="isReadOnly" class="flex flex-row items-center">
                                    <div class="border-2 border-transparent rounded-full size-4 border-t-green-500 custom-spinner"></div>
                                </div>
                            </div>
                        </div>

                        <!-- FILTROS -->
                        <div
                            class="w-full md:w-fit"
                            v-if="!filterOptions.includes('noFilters')"
                        >
                            <ButtonAction @click="showFiltersModal()" class="w-full md:w-fit" size="sm">
                                <IconFilterSolid class="size-4" />
                                Filtros
                            </ButtonAction>
                        </div>

                        <!-- ACTIONS -->
                        <div
                            class="w-full md:w-fit"
                            v-if="isImporting || isExporting"
                        >
                            <div class="flex flex-wrap items-center gap-2 md:gap-3">
                                <button
                                    v-if="isImporting && !isLoading"
                                    :disabled="isReadOnly"
                                    @click="openImportModal"
                                    title="Importar"
                                    :class="isReadOnly ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 hover:border-primary hover:text-primary'"
                                    class="flex items-center justify-center flex-grow md:flex-grow-0 gap-2 px-3 py-1 text-sm font-medium transition-all duration-200 bg-white border border-gray-300 rounded-full group"
                                >
                                    <HeroiconArrowDownOnSquareStack class="size-5 group-hover:scale-110 transition-transform" />
                                    <span>Importar</span>
                                </button>

                                <button
                                    v-if="isExporting && !isLoading"
                                    @click="exportExcelAction(excelHeaders, excelSlug)"
                                    :disabled="isReadOnly"
                                    title="Exportar Excel"
                                    :class="isReadOnly ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 hover:border-green-600 hover:text-green-600'"
                                    class="flex items-center justify-center flex-grow md:flex-grow-0 gap-2 px-3 py-1 text-sm font-medium transition-all duration-200 bg-white border border-gray-300 rounded-full group"
                                >
                                    <IconMSExcel class="size-4 group-hover:scale-110 transition-transform" />
                                    <span>Exportar en Excel</span>
                                </button>

                                <div v-if="isLoading" class="flex items-center px-3 py-1 bg-white border border-gray-300 rounded-full">
                                    <div class="border-2 border-transparent rounded-full size-4 border-t-green-500 custom-spinner"></div>
                                </div>


                            </div>
                        </div>
                    </div>


            </div>

                <!-- EXTRA ACTIONS SLOT -->
                <div class="flex-shrink-0 flex items-center h-full w-full md:w-fit gap-2 order-3 md:order-2">
                    <slot name="extra-actions"></slot>
                </div>

                <!-- ADD BUTTON -->
                <div
                    v-if="!filterOptions.includes('noAddButton')"
                    class="flex-shrink-0 flex items-center h-full w-full md:w-fit order-2 md:order-3"
                >
                    <ButtonAction
                        v-if="filterOptions.includes('isCancellable')"
                        size="sm"
                        @action="loadData"
                        class="w-full md:w-fit px-3 text-sm"
                    >
                        {{ isAdd && !isEdit ? 'Cancelar' : 'Añadir' }}
                    </ButtonAction>

                    <ButtonAction
                        v-else-if="!addTarget"
                        size="sm"
                        @action="loadData"
                        class="w-full md:w-fit px-3"
                    >
                        <span class="flex flex-row gap-1 items-center justify-center">
                            <IconPlus class="size-5" />
                            Añadir
                        </span>
                    </ButtonAction>

                    <ButtonRedirect
                        v-else
                        size="sm"
                        :to="{ name: addTarget }"
                    >
                        <span class="flex flex-row gap-1 items-center">
                            <IconPlus class="size-5" />
                            Añadir
                        </span>
                    </ButtonRedirect>
                </div>
			</div>

				<!-- ACTIVE FILTERS -->
				<div
					v-show="!isFiltersEmpty"
                    class="flex flex-wrap items-start w-full gap-3"
				>
                    <div class="flex flex-wrap w-full gap-2 ">
						<div class="flex flex-row gap-1 p-1 text-gray-700 items-center">
							<HeroiconXMarkCircle
								class="hover:text-gray-600 size-5"
								@click="removeFilters"
							/>
							<span class="flex-shrink-0 font-semibold">Filtros activos</span>
						</div>
                        <!-- Specific search by fields -->
                        <div v-if="filters.stringSearch && inlineTags.length > 0" class="flex flex-wrap gap-1 bg-gray-200 rounded-md px-1 py-1">
                            <FilterTag
                                v-for="(tag, index) in inlineTags"
                                :key="'tag-' + index"
                                :title="tag.field"
                                :value="tag.value"
                                @click:remove-filter="removeInlineTag(index)"
                                :customClass="[
                                    'h-6 flex items-center px-2 text-xs border rounded-md m-0',
                                    tag.valid ? 'bg-blue-600 text-white' : 'bg-red-600 text-white'
                                ].join(' ')"
                            />
                        </div>

                        <div v-if="filters.stringSearch && filters.stringSearch != undefined && cleanSearch" class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md">
                            <FilterTag
                                v-for="(tag, index) in inlineTags"
                                :key="'tag-' + index"
                                :title="tag.field"
                                :value="tag.value"
                                @click:remove-filter="removeInlineTag(index)"
                                :customClass="[
                                    'h-6 flex items-center px-2 text-xs border rounded-md m-0',
                                    tag.valid ? 'bg-blue-600 text-white' : 'bg-red-600 text-white'
                                ].join(' ')"
                            />

                            <FilterTag
                                v-if="cleanSearch"
                                :title="'Buscar por'"
                                :value="cleanSearch"
                                @click:remove-filter="removeFilter('stringSearch')"
                                :customClass="'h-6 flex items-center px-2 text-xs bg-gray-700 text-white border rounded-md m-0'"
                            />
                        </div>

						<!-- Role -->
                        <div v-if="filters.role" class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md">
                            <FilterTag
                                v-if="filters.role"
                                :title="'rol'"
                                :value="filters.roleObject.name"
                                @click:remove-filter="removeFilter('role')"
                                :customClass="'h-6 flex items-center px-2 text-xs bg-gray-700 border rounded-md m-0'"
                            />
                        </div>

                        <!-- Status -->
                        <div
                            v-if="filters.actionsObject"
                            class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md divide-y-1 md:divide-y-0 md:divide-x-1"
                        >
                            <FilterTag
                                v-if="filters.actionsObject"
                                :title="'Acciones'"
                                :customClass="'border bg-gray-700 rounded-md text-xs m-0'"
                                @click:remove-filter="removeFilter('actions')"
                            />

                            <span v-if="filters.actionsObject" class="flex flex-wrap gap-1 divide-y-0 md:flex-row divide:y-1 md: md:divide-x-1">
                                <template v-for="(element, index) of filters.actionsObject" :key="index">
                                    <FilterTag
                                        :title="element.name"
                                        :value="''"
                                        @click:remove-filter="removeFilter('actions', index)"
                                        customClass="bg-gray-500 rounded-md text-xs m-0"
                                    />
                                </template>
                            </span>
                        </div>



                        <div
							v-if="filters.usersObject"
                            class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md divide-y-1 md:divide-y-0 md:divide-x-1"
						>
                            <!-- Users -->
                            <FilterTag
                                v-if="filters.usersObject"
                                :title="'Usuarios'"
                                 :customClass="'border bg-gray-700 rounded-md text-xs m-0'"
                                @click:remove-filter="removeFilter('user')"
                            />

                            <!-- User -->
                            <span v-if="filters.usersObject" class="flex flex-wrap gap-1 divide-y-0 md:flex-row divide:y-1 md: md:divide-x-1">
                                <template v-for="(element, index) of filters.usersObject" :key="index">
                                    <FilterTag
                                        :title="element.name"
                                        :value="''"
                                        @click:remove-filter="removeFilter('user', index)"
                                        customClass="bg-gray-500 rounded-md text-xs m-0"
                                    />
                                </template>
                            </span>
                        </div>



						<!-- Is Enabled -->
                        <div v-if="filters.is_enabled" class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md">
                            <FilterTag
                                v-if="filters.is_enabled"
                                :title="filters.is_enabled ? 'Habilitados' : 'Todos'"
                                :value="''"
                                :customClass="'h-6 flex items-center px-2 text-xs bg-gray-700 border rounded-md m-0'"
                                @click:remove-filter="removeFilter('is_enabled')"
                            />
                        </div>

						<!-- Revisions -->
                        <div v-if="filters.revisions" class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md">
                            <FilterTag
                                v-if="filters.revisions"
                                :title="filters.revisions ? revisionsLabelChecked : revisionsLabelUnchecked"
                                :value="''"
                                :customClass="'h-6 flex items-center px-2 text-xs bg-gray-700 border rounded-md m-0'"
                                @click:remove-filter="removeFilter('revisions')"
                            />
                        </div>

						<!-- solved -->
                        <div v-if="filters.solved" class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md">
                            <FilterTag
                                v-if="filters.solved"
                                :title="filters.solved ? 'Pendientes' : 'Todas'"
                                :value="''"
                                @click:remove-filter="removeFilter('solved')"
                                :customClass="'h-6 flex items-center px-2 text-xs bg-gray-700 border rounded-md m-0'"
                            />
                        </div>

						<!-- Incidence Type -->


                        <div
							v-if="filters.devicesObject"
                            class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md divide-y-1 md:divide-y-0 md:divide-x-1"
						>
                            <!-- Devices -->
                            <FilterTag
                                v-if="filters.devicesObject"
                                :title="'Equipo'"
                                :customClass="'border bg-gray-700 rounded-md text-xs m-0'"
                                @click:remove-filter="removeFilter('device')"
                            />

                            <!-- Device -->
                            <span v-if="filters.devicesObject" class="flex flex-wrap gap-1 divide-y-0 md:flex-row divide:y-1 md: md:divide-x-1">
                                <template v-for="(element, index) of filters.devicesObject" :key="index">
                                    <FilterTag
                                        :title="element.name"
                                        :value="''"
                                        @click:remove-filter="removeFilter('device', index)"
                                        customClass="bg-gray-500 rounded-md text-xs m-0"
                                    />
                                </template>
                            </span>
                        </div>

                        <div
							v-if="filters.manufacturersObject"
                            class="flex flex-wrap gap-1 px-1 py-1 bg-gray-200 rounded-md divide-y-1 md:divide-y-0 md:divide-x-1"
						>
                            <!-- Manufacturers -->
                            <FilterTag
                                v-if="filters.manufacturersObject"
                                :title="'Fabricante'"
                                :customClass="'border bg-gray-700 rounded-md text-xs m-0'"
                                @click:remove-filter="removeFilter('manufacturer')"
                            />

                            <!-- Manufacturers -->
                            <span v-if="filters.manufacturersObject" class="flex flex-wrap gap-1 divide-y-0 md:flex-row divide:y-1 md: md:divide-x-1">
                                <template v-for="(element, index) of filters.manufacturersObject" :key="index">
                                    <FilterTag
                                        :title="element.name"
                                        :value="''"
                                        @click:remove-filter="removeFilter('manufacturer', index)"
                                        customClass="bg-gray-500 rounded-md text-xs m-0"
                                    />
                                </template>
                            </span>
                        </div>






					</div>

				</div>

		<!-- FILTER MODAL -->
		<TheModal
			:open="isFilterModalOpen"
			:title="`FILTRAR ${section}`"
			@close="isFilterModalOpen = false"
            :large="true"
            :buttons="false"
		>
			<template #body>
				<form @submit.prevent="emitFilters">
					<div class="flex flex-col gap-8">
                        <div class="flex flex-col gap-4" v-if="['user', 'role'].some(option => filterOptions.includes(option))">
                            <h3 class="pb-2 text-sm font-bold text-gray-700 uppercase border-b border-gray-100">Información General</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- USERS FILTER SELECT -->
                                <FilterMultiselect
                                    v-if="filterOptions.includes('user')"
                                    :filter="workingFilters.usersObject"
                                    :title="'Usuarios Asignados'"
                                    :options="availableUsers"
                                    :label="'name'"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :trackBy="'id'"
                                    @input="selectUser"
                                />

                                <!-- ROLE FILTER SELECT -->
                                <FilterMultiselect
                                    v-if="filterOptions.includes('role')"
                                    :filter="workingFilters.roleObject"
                                    :title="'Rol'"
                                    :options="availableRoles"
                                    :label="'name'"
                                    :trackBy="'level'"
                                    @input="selectRole"
                                />
                            </div>
                        </div>



                        <!-- SECTION 2: ESTADO Y SEGUIMIENTO -->
                        <div class="flex flex-col gap-4" v-if="['actions'].some(option => filterOptions.includes(option))">
                            <h3 class="pb-2 text-sm font-bold text-gray-700 uppercase border-b border-gray-100">Estado y Seguimiento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- PERMISSIONS ACTIONS FILTER SELECT -->
                                <FilterMultiselect
                                    v-if="filterOptions.includes('actions')"
                                    :filter="workingFilters.actionsObject"
                                    :title="'Acciones'"
                                    :options="availableActions"
                                    :label="'name'"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :trackBy="'id'"
                                    @input="selectActions"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col gap-4" v-if="['date_published', 'supply_date'].some(option => filterOptions.includes(option))">
                            <h3 class="pb-2 text-sm font-bold text-gray-700 uppercase border-b border-gray-100">Fechas</h3>
                            <div class="flex flex-col gap-6">
                                <!-- OTHER DATE TOGGLES -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-gray-50 rounded-lg" v-if="['date_published', 'supply_date'].some(option => filterOptions.includes(option))">
                                    <InputWrapper label="Fecha de publicación" v-if="filterOptions.includes('date_published') || filterOptions.includes('supply_date')">
                                        <InputToggleButton
                                            :name="workingFilters.date_published == true ? 'Habilitado' : 'Todos'"
                                            :value="workingFilters.date_published"
                                            @change="selectDate_published"
                                        />
                                    </InputWrapper>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: OTROS FILTROS -->
                        <div class="flex flex-col gap-4" v-if="['manufacturer', 'device', 'is_enabled'].some(option => filterOptions.includes(option))">
                            <h3 class="pb-2 text-sm font-bold text-gray-700 uppercase border-b border-gray-100">Información Técnica y Otros</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- MANUFACTURER FILTER SELECT -->
                                <FilterMultiselect
                                    v-if="filterOptions.includes('manufacturer')"
                                    :filter="workingFilters.manufacturersObject"
                                    :title="'Fabricante'"
                                    :options="manufacturersOptions"
                                    :label="'name'"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :trackBy="'id'"
                                    @input="selectManufacturer"
                                />

                                <!-- DEVICE FILTER SELECT -->
                                <FilterMultiselect
                                    v-if="filterOptions.includes('device')"
                                    :filter="workingFilters.devicesObject"
                                    :title="'Equipo'"
                                    :options="devicesOptions"
                                    :label="'name'"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :trackBy="'id'"
                                    @input="selectDevice"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-gray-50 rounded-lg">
                                <!-- TECHNICAL TOGGLES -->
                                <InputWrapper label="Estado equipos" v-if="filterOptions.includes('is_enabled')">
                                    <InputToggleButton
                                        :name="workingFilters.is_enabled == true ? 'Habilitados' : 'Todos'"
                                        :value="workingFilters.is_enabled"
                                        @change="isSelectEnabled"
                                    />
                                </InputWrapper>
                            </div>
                        </div>
                    </div>
				</form>
			</template>

			<template #action>
				<span class="flex w-full rounded-md shadow-sm sm:w-auto">
					<ButtonAction
						type="button"
                        size="sm"
                        color="green"
						@click="emitFilters()"
					>
						{{ 'Filtrar' }}
					</ButtonAction>
				</span>
			</template>
		</TheModal>
		<TheModal
            :open="isImportModalOpen"
            :title="`IMPORTAR ${section.toUpperCase()}`"
            @close="handleCloseImportModal"
            :large=true
        >
            <template #body>
                <div class="flex flex-col gap-6 p-2">
                    <!-- SECTION 1: UPLOAD -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">1. Selección de archivo</h2>
                            <div v-if="fileUploaded" class="flex items-center gap-2 text-[10px] text-green-600 font-bold uppercase">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                Cargado
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="flex-1 w-full">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Paso inicial</label>
                                    <div class="flex items-center gap-3">
                                        <label
                                            for="excelToImport"
                                            class="inline-flex items-center gap-2 cursor-pointer rounded-lg px-6 py-2.5 bg-primary text-white text-xs font-bold shadow-sm hover:brightness-105 active:scale-95 transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            {{ fileUploaded ? 'Cambiar Archivo' : 'Seleccionar Excel' }}
                                        </label>
                                        <input type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" @change="handleExcelFileUpload($event)" id="excelToImport" class="hidden">

                                        <button
                                            @click="generateExcelTemplate()"
                                            class="text-xs font-bold text-gray-500 hover:text-primary px-4 py-2.5 transition-colors border border-gray-200 rounded-lg bg-white"
                                        >
                                            Descargar Plantilla
                                        </button>
                                    </div>
                                </div>

                                <div v-if="fileUploaded" class="flex-1 w-full bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-tighter">Archivo seleccionado</p>
                                        <p class="text-xs text-blue-600 truncate font-medium">{{ fileUploaded }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: PREVIEW & VALIDATION -->
                    <div v-if="fileUploaded" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">2. Previsualización y Validación</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Duplicate Rows / Errors -->
                            <div v-if="duplicatedRows.length > 0 || errorMessage" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    <div>
                                        <p class="text-xs font-bold text-red-800">{{ errorMessage || 'Se han detectado errores en el archivo' }}</p>
                                        <ul v-if="duplicatedRows.length > 0" class="mt-2 space-y-1">
                                            <li v-for="(row, index) of duplicatedRows" :key="index" class="text-[10px] text-red-700">
                                                Fila {{row.rowNumber}}: <span class="font-bold whitespace-nowrap" v-for="(element, eIdx) of row.result" :key="eIdx">{{element.name}}: {{element.value}}{{ eIdx < row.result.length - 1 ? ', ' : '' }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <button
                                        class="text-[11px] font-bold text-primary hover:underline flex items-center gap-1.5"
                                        @click="isShowingExcelPreview = !isShowingExcelPreview"
                                    >
                                        <svg class="w-3.5 h-3.5 transform transition-transform" :class="{ 'rotate-180': isShowingExcelPreview }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        {{ isShowingExcelPreview ? 'Ocultar Contenido' : 'Ver Contenido del Excel' }}
                                    </button>
                                    <div v-if="isShowingExcelPreview" class="mt-4">
                                        <CustomExcelTable :data="arrayExcelFiles" />
                                    </div>
                                </div>

                                <div v-if="infoImportCustomer && (infoImportCustomer.valid_customers?.length > 0 || infoImportCustomer.invalid_customers?.length > 0)">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Validación de Clientes</h3>
                                    </div>
                                    <CustomerImportValidationTable :data="infoImportCustomer" />
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </template>

            <template #action>
                <div class="flex items-center gap-3">
                    <ButtonAction
                        :disabled="!fileUploaded"
                        @click="importExcelAction()"
                        size="sm"
                    >
                        Procesar Importación
                    </ButtonAction>
                </div>
            </template>
        </TheModal>

        <TheModal :open="isDeleteFilterModalOpen" title="Borrar filtros personalizados" destructive @close="isDeleteFilterModalOpen = false">
            <template #body>
                <p class="pr-8 leading-7 tracking-wide text-gray-800">
                    SE VAN A ELIMINAR TODOS LOS FILTROS PERSONALIZADOS
                    <br>¿Está usted seguro?
                    <br>Esta acción no se podrá deshacer
                </p>
            </template>

            <template #action>
                <ButtonAction
                    :label="'Borrar'"
                    color="red"
                    size="sm"
                    @click="removeAllCustomFilter"
                />
            </template>
        </TheModal>
	    </div>
    </div>
</template>

<script>
import Multiselect from "vue-multiselect";
import AdminMixin from "@/components/mixins/AdminMixin.vue";
import { Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import Excel from 'exceljs';

export default {
	name: "FilterControls",

	mixins: [AdminMixin],

	components: {
		Multiselect,
		"VCalendar": Calendar,
    	"VDatePicker": DatePicker,
	},

	props: {
		section: {
			type: String,
			default: "",
		},
		addTarget: {
			type: [String, Boolean],
			default: "",
		},
		filterOptions: {
			type: Array,
			default: () => [],
		},
		availableRoles: {
			type: Array,
			default: () => [],
		},
		availableActions: {
			type: Array,
			default: () => [],
		},

		availableStatus: {
			type: Array,
			default: () => [],
		},
		availableWithoutStatus: {
			type: Array,
			default: () => [],
		},
		availableTypes: {
			type: Array,
			default: () => [],
		},

		devicesOptions: {
			type: Array,
			default: () => [],
		},
		manufacturersOptions: {
			type: Array,
			default: () => [],
		},

		availableUsers: {
			type: Array,
			default: () => [],
		},


		// defaultFilters: {
		// 	type: Object,
		// 	default: () => {},
		// },
		isEdit: {
			type: Boolean,
			default: false,
		},
		isAdd: {
			type: Boolean,
			default: false,
		},
		isReadOnly: {
			type: Boolean,
			default: false,
		},
        // boolean to display the button for import
		isImporting: {
			type: Boolean,
			default: false,
		},
        // array that contains the template excel structure
        templateExcelHeaders: {
            type: Array,
            default: () => []
        },
        // array that contains the excel structure
        excelHeaders: {
            type: Array,
            default: () => []
        },
        // to specify what key or key pairs will be unique
        arraySpecificKeys: {
            type: Array,
            default: () => []
        },
        // to specify what keys will be shown in the infoSection component
        arrayKeysForError: {
            type: Array,
            default: () => []
        },
        // boolean to display the button for export to excel
		isExporting: {
			type: Boolean,
			default: false,
		},


        excelSlug: {
			type: Object,
			default: () => ({slug: '', payload: ''}),
		},
        infoImportCustomer: {
            type: Object,
            default: () => [],
        },
        permissionSection: {
            type: String,
        },

	},

    data() {
		return {
			availableCategories: [],
			stringSearch: undefined,
			isFilterModalOpen: false,
			filters: {},
			workingFilters: {},
			is_enabled: false,
			solved: false,
			isAdding: false,
            defaultFilters: {},
            isImportModalOpen: false,
            duplicatedRows: [],
            arrayExcelFiles: [],
            isShowingExcelPreview: false,
            errorMessage: '',
            fileUploaded: '',
            isLoading: false,
            debounceTimeout: null,
            timeout: null,
            componentsList: [],
            isDeleteFilterModalOpen: false,
            activeFilter: null,
            infoImport: false,
            info: "",
            filterArray: [
                { id: 1, name: 'Nombre' },
                { id: 2, name: 'NIF' },
                { id: 3, name: 'Código KD' },

                { id: 5, name: 'Teléfono' },
                { id: 7, name: 'Email' },
            ],
            isEmpty: false,
            startText: 'Desde',
            endText: 'Hasta',
            fieldMap: [


                // Customers
                { key: 'name', fields: ['customers.name'], origin: 'customers' },
                { key: 'nif', fields: ['customers.nif'], origin: 'customers' },
                { key: 'email', fields: ['customers.email', 'customers.email2'], origin: 'customers' },
                { key: 'phone', fields: ['customers.phone', 'customers.phone2'], origin: 'customers' },

            ],
            activeQuickFilter: null,
            importStep: 1,
        };
    },

	computed: {
        debouncedFilterEmit() {
            clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    this.applyFilters();
            }, 500);
        },

        applyFilters() {
            return this.emitFilters;
        },

		isFiltersEmpty() {
			let auxFilters = this.filters;

			/* Iterate through the object properties and remove them if they're empty */
			for (const key in auxFilters) {
				if (Object.hasOwnProperty.call(auxFilters, key)) {
					const property = auxFilters[key];
					if (
						property == undefined
						|| property === ""
						|| property === null
					) {
						delete auxFilters[key];
					}
				}
			}

			const auxLength = Object.keys(auxFilters).length;
			const defaultsLength = this.defaultFilters ? Object.keys(this.defaultFilters).length : 0;

			const offset = 0;
			if (auxFilters == undefined
				|| this.user?.level >= 20 && auxLength <= (offset + defaultsLength)

			) {
				return true;
			}

			return auxLength <= defaultsLength;
		},

        customClassFilter() {

            if (!tagField) {
                return 'h-6 flex items-center px-2 text-xs bg-gray-400 text-white border rounded-md m-0'
            }

            const exists = this.fieldMap.some(f => f.key === tagField && f.origin === this.$route.name);

            return exists
                ? 'h-6 flex items-center px-2 text-xs bg-blue-600 text-white border rounded-md m-0'
                : 'h-6 flex items-center px-2 text-xs bg-red-600 text-white border rounded-md m-0'
        },

        inlineTags() {
            const search = this.filters.stringSearch || "";
            const tags = [];

            const validFields = this.fieldMap
                .filter(f => f.origin === this.$route.name)
                .map(f => f.key);

            // const tagRegex = /([\w\-_]+):"((?:[^"]|"(?!\s[\w\-_]+:"))*)"/g;
            const tagRegex = /\b([\w\-_]+):"([^"]*)"(?:,?"([^"]*)")*/g;
            let match;

            while ((match = tagRegex.exec(search)) !== null) {
                const field = match[1];
                const rawValues = match[2];
                const values = rawValues.split('","').map(v => v.trim());

                // Determine if the field is valid
                const isValid = validFields.includes(field);

                values.forEach(value => {
                    tags.push({ field, value, valid: isValid });
                });
            }

            return tags;
        },

        cleanSearch() {
            const search = this.filters.stringSearch || "";

            const tagRegex = /[\w\-_]+:"((?:[^"]|"(?!\s[\w\-_]+:"))*)"/g;
            return search.replace(tagRegex, '').replace(/\s{2,}/g, ' ').trim();
        },
	},

	watch: {
        isFilterModalOpen(val) {
            if (val) {
                this.loadSavedFilters();
            }
        },
		isEdit: {
			deep: true,
			immediate: true,
			handler(value) {
				this.is_enabled = value;
				this.solved = value;
			},

		},
		isAdd: {
			deep: true,
			immediate: true,
			handler(value) {
				this.isAdding = value;
			},
		},
		defaultFilters() {
		},
	},

    async mounted() {
        const savedFilters = JSON.parse(localStorage.getItem("filters")) || {};
        const validRoutes = ['customers', 'devices'];

        if (validRoutes.includes(this.$route.name)) {
            const routeFilters = savedFilters[this.$route.name];
            if (routeFilters) {

                // Convert stored filter arrays back to objects
                const filterKeysToConvert = [
                    'devicesObject', 'manufacturersObject',
                    'usersObject', 'roleObject',
                ];

                filterKeysToConvert.forEach(key => {
                    const value = routeFilters[key];
                    // Only convert arrays to objects
                    if (Array.isArray(value)) {
                        routeFilters[key] = Object.fromEntries(value.map((item, index) => [index, item]));
                    }
                });
            }

            this.filters = { ...routeFilters };
        } else {
            this.filters = { ...this.filters, ...this.defaultFilters };
        }

        this.workingFilters = { ...this.filters };
        this.is_enabled = this.isEdit;
        this.revisions = this.isEdit;
        this.solved = this.isEdit;
        this.isAdding = this.isAdd;
        let filtersArray = JSON.parse(localStorage.getItem("customFilters")) || [];
        filtersArray.forEach(() => this.componentsList.push('IconCustomFilter'));
        this.setDefaultFilters();
    },

	methods: {
		parseDateTime(value){
            const date = new Date(value);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');

            return `${day}/${month}/${year}`;
		},

		showFiltersModal() {
			this.workingFilters = this.filters;
			this.filters = { ...this.filters };

			this.isFilterModalOpen = true;
		},

		setDefaultFilters() {
			// this.workingFilters = {...this.workingFilters, ...this.defaultFilters};
			for (const key in this.defaultFilters) {

				if (Object.hasOwnProperty.call(this.defaultFilters, key)) {
					const element = this.defaultFilters[key];
                    this.workingFilters[key] = element;
				}
			}
			// if (this.defaultFilters.pending_publication == undefined) {
			// 	this.defaultFilters.pending_publication = 2;
			// }
		},

		emitFilters() {
            if (this.workingFilters.is_enabled == false) delete this.workingFilters.is_enabled;
            if (this.workingFilters.revisions == false) delete this.workingFilters.revisions;
            if (this.workingFilters.solved == false) delete this.workingFilters.solved;

            if (this.storedFilterName) sessionStorage.removeItem(`${this.storedFilterName}`);
            this.filters = { ...this.workingFilters };

            let filtersToEmit = {
                ...this.workingFilters,
            };

            // Convert object filters to arrays
            let objectFilters = [
                'usersObject',
                'devicesObject', 'manufacturersObject',
                'roleObject', 'justificationsObject'
            ];

            objectFilters.forEach(key => {
                const value = filtersToEmit[key];

                if (!value) {
                    filtersToEmit[key] = [];
                    return;
                }

                if (Array.isArray(value)) {
                    filtersToEmit[key] = value
                        .filter(item => item && typeof item === 'object')
                        .map(item => ({ id: item.id, name: item.name }));
                    return;
                }

                if (typeof value === 'object') {
                    filtersToEmit[key] = Object.values(value)
                        .filter(item => item && typeof item === 'object')
                        .map(item => ({ id: item.id, name: item.name }));
                    return;
                }

                filtersToEmit[key] = [];
            });

            const normalizeAssociateFilters = (objectKey, targetKey) => {
                const value = filtersToEmit[objectKey];

                if (!Array.isArray(value) || value.length === 0) {
                    return;
                }

                const ids = value
                    .map(item => item.id)
                    .filter(Boolean);

                if (ids.length) {
                    filtersToEmit[targetKey] = ids;
                }
            };



            // Remove empty values
            Object.entries(filtersToEmit).forEach(([key, value]) => {
                if (value == {} || (Array.isArray(value) && value.length === 0) || value == null) {
                    delete filtersToEmit[key];
                }
            });

            // Save to localStorage for specific routes
            const savedFilters = JSON.parse(localStorage.getItem("filters")) || {};
            const validRoutes = ['projects', 'customers', 'devices'];

            if (validRoutes.includes(this.$route.name)) {
                savedFilters[this.$route.name] = filtersToEmit;
                localStorage.setItem("filters", JSON.stringify(savedFilters));
            }

            // Remove internal-use object keys before emitting
            objectFilters.forEach(key => {
                delete filtersToEmit[key];
            });


            // Adjust date fields to UTC
            function adjustDateToUTC(dateString, isStartOfDay) {
				let date = new Date(dateString);

                isStartOfDay ? date.setHours(0, 0, 0, 0) : date.setHours(23, 59, 59, 999);

				// Get the timezone offset in minutes and convert it to milliseconds
				let timezoneOffset = date.getTimezoneOffset() * 60 * 1000;

				// Adjust the date by subtracting the offset
				let adjustedDate = new Date(date.getTime() - timezoneOffset);

                return adjustedDate;
            }


            // Map through fields and adjust them dynamically
            const dateFields = [
                { key: "start_date_validated", isStartOfDay: true },
                { key: "end_date_validated", isStartOfDay: false },
                { key: "start_date_published", isStartOfDay: true },
                { key: "end_date_published", isStartOfDay: false },
            ];

            dateFields.forEach(({ key, isStartOfDay }) => {
                if (filtersToEmit[key]) {
                    filtersToEmit[key] = adjustDateToUTC(filtersToEmit[key], isStartOfDay);
                }
            });
            this.$emit("update:filters", filtersToEmit);
            this.isFilterModalOpen = false;
            // When applying from modal, we reset quick filter styles as it might be a complex filter
            this.activeQuickFilter = null;
            this.activeFilter = null;
        },

        openImportModal() {
            this.duplicatedRows = [];
            this.arrayExcelFiles = [];
            this.fileUploaded = '';
            this.errorMessage = '';
            this.importStep = 1;
            this.isImportModalOpen = true;
        },

        async quickAccess(array_types) {
            this.activeFilter = null;
            if (!this.workingFilters) this.workingFilters = {};

            this.workingFilters = {};
            this.workingFilters.typesObject = {};
            this.workingFilters.type = [];

            // Tracking for UI feedback
            this.activeQuickFilter = JSON.stringify(array_types.sort());

            array_types.forEach(type => {
                this.workingFilters.typesObject[type] = this.availableTypes.find(f => f.id == type);
                this.workingFilters.type.push(type);
            });

            this.filters = { ...this.workingFilters };
            this.emitFilters();
        },

        isQuickFilterActive(array_types) {
            return this.activeQuickFilter === JSON.stringify(array_types.sort());
        },

        async customizeFilter() {
            if (!this.workingFilters) this.workingFilters = {};

            this.filters = { ...this.workingFilters };

            if(Object.keys(this.filters).length >0){

                if (this.componentsList.length<=3){
                    this.componentsList.push('IconCustomFilter');
                    this.activeFilter=this.componentsList.length-1;
                    const filtersArray = JSON.parse(localStorage.getItem("customFilters")) || [];
                    filtersArray.push(this.filters);

                    localStorage.setItem("customFilters", JSON.stringify(filtersArray));
                    this.openAlert(
                        "success",
                        "Filtro personalizado creado",
                        null
                    );
                } else {
                    this.openAlert(
                            "error",
                            "Se ha alcanzado el límite de filtros personalizados",
                            null
                    );
                }
            } else{
                this.openAlert(
                    "error",
                    "Aplica algún filtro para crear un filtro personalizado",
                    null
                );
            }
        },

        removeCustomFilter(index){
            this.componentsList.splice(index, 1);
            this.activeFilter=null;
            const filtersArray = JSON.parse(localStorage.getItem("customFilters"));
            if (Array.isArray(filtersArray) && filtersArray.length > 0) {
                filtersArray.splice(index, 1);
                localStorage.setItem("customFilters", JSON.stringify(filtersArray));
            }
            if (this.componentsList.length==0) localStorage.removeItem("customFilters");
            this.openAlert(
                    "info",
                    "Filtro personalizado eliminado",
                    null
			    );
        },

        openDeleteFilterModal(){
            this.isDeleteFilterModalOpen=true;
        },

        removeAllCustomFilter(){
            this.componentsList= [];
            localStorage.removeItem("customFilters");
             this.isDeleteFilterModalOpen=false;
                this.openAlert(
                    "info",
                    "Se han eliminado todos los filtros personalizados",
                    null
			    );
        },

        handleComponentClick(index) {
            this.activeFilter = index;
            this.activeQuickFilter = null; // Reset quick filter when custom is selected
            const filtersArray = JSON.parse(localStorage.getItem("customFilters"));
            this.workingFilters = filtersArray[index];
            this.filters = { ...this.workingFilters };
            this.emitFilters();
        },


        async generateExcelTemplate(){
            const workbook = new Excel.Workbook();
            const worksheet = workbook.addWorksheet('My Sheet');
            worksheet.columns = this.templateExcelHeaders;

            this.templateExcelHeaders.forEach(column => {
                worksheet.addRow(column.header);
            })

            // <><><> EXCEL STYLES <><><>
            const cells=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];

            // Define the colors for fields
            const fieldsColor = '4F81BB'; // blue

            this.templateExcelHeaders.map((column, index) => {

                const cell = worksheet.getCell(cells[index] + '1');

                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: fieldsColor }
                };

                cell.font = {
                    color: { argb: 'FFFFFF' },
                    bold: true
                };

                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'start'
                };

                // Text data type for certain columns
                const textColumns = [
                    5,  // F (postal_code)
                ];

                if (textColumns.includes(index)) {
                    worksheet.getColumn(index + 1).numFmt = '@';
                }

            });

            const buffer = await workbook.xlsx.writeBuffer();
            const fileType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8';
            const fileName = 'plantillaExcel';
            const blob = new Blob([buffer], {type: fileType});
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${fileName}.xlsx`;
            link.click();
            link.remove();
        },

        handleExcelFileUpload(e) {
            if (e.target.files.length > 0) {
                this.handleExcelData(e.target.files[0]);
                // Reset the input so same file name triggers change again
                e.target.value = null;
            }
        },

        importExcelAction(){
            const validData = this.arrayExcelFiles.filter(row => !this.duplicatedRows.includes(row));
            this.$emit('import-from-excel', validData);
            this.infoImport = true;
        },

        handleExcelData(file){
            this.$emit('clearInfoImportCustomer');
            this.duplicatedRows = [];
            this.arrayExcelFiles = [];
            this.errorMessage = '';
            const reader = new FileReader();
            let array = [];
            reader.onload = async (event) => {
                const arrayBuffer = event.target.result;

                const workbook = new Excel.Workbook();
                await workbook.xlsx.load(arrayBuffer);

                const worksheet = workbook.getWorksheet(1);

                // Get headers from first row to define the structure of the uploaded file
                const headerRow = worksheet.getRow(1);
                const fileColumns = [];
                headerRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                    const headerName = cell.value && cell.value.text ? cell.value.text : (cell.value || '');

                    // Match with template headers by name for identification
                    const templateCol = this.templateExcelHeaders.find(tc =>
                        tc.header && headerName && tc.header.toString().toLowerCase() === headerName.toString().toLowerCase()
                    );

                    fileColumns.push({
                        colNumber,
                        name: headerName,
                        key: templateCol ? templateCol.key : `extra_${colNumber}`,
                        templateHeader: templateCol ? templateCol.header : headerName
                    });
                });

                worksheet.eachRow({ includeEmpty: true }, (row, rowNumber) => {
                    // Skip the first row
                    if (rowNumber === 1) return;

                    let rowObject = { rowNumber, values: [] };

                    // Iterate through all columns found in the header row
                    fileColumns.forEach((col) => {
                        const cell = row.getCell(col.colNumber);
                        let rawValue = cell.value && cell.value.text ? cell.value.text : cell.value;

                        // Date formatting for certain fields if needed
                        if (rawValue instanceof Date) {
                            const day = String(rawValue.getDate()).padStart(2, '0');
                            const month = String(rawValue.getMonth() + 1).padStart(2, '0');
                            const year = rawValue.getFullYear();
                            rawValue = `${day}/${month}/${year}`;
                        }

                        rowObject.values.push({
                            key: col.key,
                            name: col.templateHeader,
                            value: rawValue !== undefined ? rawValue : null
                        });
                    });

                    // Add only if row is not entirely empty
                    if (rowObject.values.length > 0 && !rowObject.values.every(f => f.value == null)) {
                        array.push(rowObject);
                    }
                });

                const specificKeys = this.arraySpecificKeys;
                const rowOccurrences = {};

                // Iterate over the rows
                array.forEach(row => {

                    const compositeKey = specificKeys.map(key => {
                        const keyValueObject = row.values.find(value => value.key === key);
                        // If the key exists, convert its value to lowercase for case-insensitive comparison
                        return keyValueObject?.value?.toString().toLowerCase() || '';
                    }).join('|'); // Join the values with a separator (e.g., '|')

                    if (compositeKey) {
                        // Initialize or increment the count for this composite key
                        if (rowOccurrences[compositeKey]) {
                            rowOccurrences[compositeKey].count += 1;
                            rowOccurrences[compositeKey].rows.push(row);
                        } else {
                            rowOccurrences[compositeKey] = { count: 1, rows: [row] };
                        }
                    }
                });

                // Collect rows that have duplicates for the specific key
                Object.values(rowOccurrences).forEach(entry => {
                    if (entry.count > 1) {
                        // If you want all instances including the original
                        this.duplicatedRows.push(...entry.rows);

                        // If you only want the duplicates (excluding the first occurrence), use:
                        // this.duplicatedRows.push(...entry.rows.slice(1));
                    } else {
                        entry.rows[0].values.map(m =>
                            this.arrayKeysForError.includes(m.key) && m.value == null
                                ? this.duplicatedRows.push(...entry.rows)
                                : m
                        );
                    }
                });

                // Show all rows in the preview, including potential duplicates
                // Highlighting will be handled by the duplicatedRows logic
                this.arrayExcelFiles = array;

                // Prepare highlighting results
                this.arrayExcelFiles.forEach(row => {
                    row.result = [];
                    row.values.forEach(element => {
                        // 1. Highlight if it's a required field and is missing
                        if (this.arrayKeysForError.includes(element.key) && (element.value === null || element.value === undefined || element.value === '')) {
                            row.result.push(element);
                        }
                        // 2. Highlight if it's a duplicate based on specific keys
                        else if (this.duplicatedRows.includes(row) && this.arraySpecificKeys.includes(element.key)) {
                            // Only highlight if it's actually a duplicate (not just missing a different required field)
                            // We know it's in duplicatedRows, so if it's not missing a field, it must be a duplicate
                            row.result.push(element);
                        }
                    });
                });

                if (this.duplicatedRows.length > 0) {
                    this.errorMessage = 'Elementos duplicados o con valores incorrectos. No se importarán los siguientes registros:';
                } else if (array.length == 0) {
                    this.errorMessage = 'Documento vacío';
                }
                // this.openAlert('success', 'Registros importados correctamente');
            };

            reader.onerror = (error) => {
                console.error('Error reading file:', error);
                this.openAlert('error', 'Error al leer el archivo');
            };

            reader.readAsArrayBuffer(file);
            this.fileUploaded = file?.name;
            this.importStep = 2;
        },

        loadSavedFilters() {
            const savedFilters = JSON.parse(localStorage.getItem("filters")) || {};
            const validRoutes = ['customers', 'devices'];

            if (validRoutes.includes(this.$route.name)) {
                const routeFilters = savedFilters[this.$route.name];
                if (routeFilters) {
                    this.filters = { ...routeFilters };
                }
            } else {
                this.filters = { ...this.filters, ...this.defaultFilters };
            }

            this.workingFilters = { ...this.filters };
        },

        openFilterModal() {
            this.loadSavedFilters();
            this.isFilterModalOpen = true;
        },

		loadData() {
			if (this.filterOptions.includes("isCancellable")) this.is_enabled = !this.is_enabled;
			if (this.filterOptions.includes("isCancellable")) this.solved = !this.solved;
			this.$emit("click:add-button");
		},

		selectStartDate(startdate) {
			// let date = new Date(startdate);
			// date = date.setHours(0, 0, 0, 0);
			// this.workingFilters.start_date_validated = new Date(date);
		},

		selectEndDate(enddate) {
			// let date = new Date(enddate);
			// date = date.setHours(23, 59, 59, 999);
			// this.workingFilters.end_date_validated = new Date(date);
		},

		selectRole(role) {
			this.workingFilters.role = role?.level;
			this.workingFilters.roleObject = role;
		},

        selectActions(actions) {
            this.workingFilters.actions = [];
            Object.keys(actions).forEach(key => {
                this.workingFilters.actions.push(actions[key].id);
            });
			this.workingFilters.actionsObject = actions;
        },



		selectStatus(status) {
            this.workingFilters.status = [];
            Object.keys(status).forEach(key => {
                this.workingFilters.status.push(status[key].id);
            })
			this.workingFilters.statusObject = status;
		},

		selectWithoutStatus(withoutStatus) {
            this.workingFilters.withoutStatus = [];
            Object.keys(withoutStatus).forEach(key => {
                this.workingFilters.withoutStatus.push(withoutStatus[key].id);
            });
			this.workingFilters.withoutStatusObject = withoutStatus;
		},









		selectUser(user) {
            this.workingFilters.user = [];
            Object.keys(user).forEach(key => {
                this.workingFilters.user.push(user[key].id);
            });
			this.workingFilters.usersObject = user;
		},




		selectDevice(device) {
            this.workingFilters.device = [];
            Object.keys(device).forEach(key => {
                this.workingFilters.device.push(device[key].id);
            });
			this.workingFilters.devicesObject = device;
		},

		selectManufacturer(manufacturer) {
            if (!Array.isArray(manufacturer)) {
                manufacturer = Object.values(manufacturer);
            }
            this.workingFilters.manufacturer = manufacturer.map(m => m.id);
            this.workingFilters.manufacturersObject = manufacturer;
		},


		isSelectEnabled(event) {
			this.workingFilters.is_enabled = event.target.checked;
		},



		selectSolved(event) {
			this.workingFilters.solved = event.target.checked;
		},



		removeFilter(filter_name, key = false) {
            let isEmpty = null;
            const isFalseButNotZero = key === false;
			switch (filter_name) {
				case "stringSearch":
                    let value = this.workingFilters.stringSearch || "";

                    // Regex for valid tags: field:"value" or field:"v1","v2"
                    const validTagRegex = /\b(\w+):"([^"]*(?:","[^"]*)*)"/g;

                    // Extract all valid tags
                    const matches = [];
                    let match;
                    while ((match = validTagRegex.exec(value)) !== null) {
                        matches.push(match[0]); // the full tag text
                    }

                    // Keep only existing tags
                    this.workingFilters.stringSearch = matches.length > 0
                        ? matches.join(" ").trim()
                        : "";

                    break;
				case "role":
					delete this.workingFilters.roleObject;
					delete this.workingFilters.role;
					break;
                case "actions":
                    if (key) {
                        delete this.workingFilters.actions[key];
                        delete this.workingFilters.actionsObject[key];
                    }

                    if (Object.keys(this.workingFilters.actions).length == 0 || !key) {
                        delete this.workingFilters.actions;
                        delete this.workingFilters.actionsObject;
                    }
                    break;

                case "status":
                    isEmpty = Object.keys(this.workingFilters.status).length === 1;
                    if (isEmpty || isFalseButNotZero) {
                        delete this.workingFilters.status;
                        delete this.workingFilters.statusObject;
                    }
                    else if (key !== false) {
                        delete this.workingFilters.status[key];
                        delete this.workingFilters.statusObject[key];
                    }
					break;
                case "withoutStatus":
                    isEmpty = Object.keys(this.workingFilters.withoutStatus).length === 1;
                    if (isEmpty || isFalseButNotZero) {
                        delete this.workingFilters.withoutStatus;
                        delete this.workingFilters.withoutStatusObject;
                    }
                    else if (key !== false) {
                        delete this.workingFilters.withoutStatus[key];
                        delete this.workingFilters.withoutStatusObject[key];
                    }
					break;


                case "is_enabled":
					delete this.workingFilters.is_enabled;
					break;

                case "solved":
					delete this.workingFilters.solved;
					break;



				case "device":
                    isEmpty = Object.keys(this.workingFilters.device).length === 1;
                    if (isEmpty || isFalseButNotZero) {
                        delete this.workingFilters.device;
                        delete this.workingFilters.devicesObject;
                    }
                    else if (key !== false) {
                        delete this.workingFilters.device[key];
                        delete this.workingFilters.devicesObject[key];
                    }
					break;
				case "manufacturer":
                    isEmpty = Object.keys(this.workingFilters.manufacturer).length === 1;
                    if (isEmpty || isFalseButNotZero) {
                        delete this.workingFilters.manufacturer;
                        delete this.workingFilters.manufacturersObject;
                    }
                    else if (key !== false) {
                        delete this.workingFilters.manufacturer[key];
                        delete this.workingFilters.manufacturersObject[key];
                    }
                    break;






				default:
					break;
			}

			this.filters = { ...this.workingFilters };
			this.emitFilters();
		},

		async removeFilters() {

			this.workingFilters = {};

			this.openAlert(
				"success",
				"Filtros eliminados",
				null
			);
			this.setDefaultFilters();
            this.activeQuickFilter = null;
            this.activeFilter = null;
			this.emitFilters();
		},





        handleCloseImportModal() {
            this.isImportModalOpen = false;
            this.isShowingExcelPreview = false;
            this.importStep = 1;
            this.$emit('clearInfoImportCustomer')
        },

        removeInlineTag(index) {
            const tags = this.inlineTags.slice();
            tags.splice(index, 1);
            this.updateStringSearch(tags, this.cleanSearch);
        },

        removeFreeText(index, tag) {
            if (!tag || !tag.field || !tag.value) return;

            let current = this.workingFilters.stringSearch || '';

            const tagRegex = new RegExp(`${tag.field}:"([^"]*(?:"[^"]*)*)"`,'g');

            let foundTag = false;

            current = current.replace(tagRegex, (match, groupValues) => {
                let values = groupValues.split('","').map(v => v.trim());

                if (values.includes(tag.value)) {
                    foundTag = true;
                }

                values = values.filter(v => v !== tag.value);

                return values.length ? `${tag.field}:"${values.join('","')}"` : '';
            });

            if (!foundTag) {
                const escapedValue = tag.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const freeTextRegex = new RegExp(`\\b${escapedValue}\\b`, 'g');
                current = current.replace(freeTextRegex, '');
            }

            current = current.replace(/\s{2,}/g, ' ').trim();

            this.workingFilters.stringSearch = current;
            this.filters = { ...this.workingFilters };
            this.emitFilters();
        },


        updateStringSearch(tags, freeText) {
            const tagMap = {};

            tags.forEach(t => {
                if (!tagMap[t.field]) tagMap[t.field] = [];
                tagMap[t.field].push(t.value);
            });

            const tagsPart = Object.entries(tagMap)
                .map(([field, values]) => `${field}:"${values.join('","')}"`)
                .join(' ');

            const combined = [tagsPart, freeText].filter(Boolean).join(' ');

            this.workingFilters.stringSearch = combined;
            this.filters = { ...this.workingFilters };

            this.emitFilters();
        },
	},
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
