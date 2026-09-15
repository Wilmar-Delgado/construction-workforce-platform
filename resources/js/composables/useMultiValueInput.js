export function mergeMultiValueInput(existingValues = [], pendingValue = '') {
    const seen = new Set(
        existingValues.map((value) => String(value).trim().toLocaleLowerCase())
    );

    const additions = String(pendingValue)
        .split(',')
        .map((value) => value.trim())
        .filter((value) => {
            if (!value || seen.has(value.toLocaleLowerCase())) {
                return false;
            }

            seen.add(value.toLocaleLowerCase());

            return true;
        });

    return [...existingValues, ...additions];
}
