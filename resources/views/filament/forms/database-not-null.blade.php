<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ nullable: {{ $applyStateBindingModifiers("\$wire.\$entangle('{$getStatePath()}')") }} }">
        <input type="checkbox" class="fi-checkbox-input" id="{{ $getId() }}" aria-label="Not Null"
            :checked="!nullable" x-on:change="nullable = !$event.target.checked" @disabled($isDisabled()) />
    </div>
</x-dynamic-component>
