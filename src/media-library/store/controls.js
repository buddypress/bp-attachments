/**
 * WordPress dependencies.
 */
const {
	apiFetch,
} = wp;

/**
 * Default export for registering the controls with the store.
 *
 * @return {Object} An object with the controls to register with the store on
 *                  the controls property of the registration object.
 */
export const controls = {
	FETCH_FROM_API( { path, parse } ) {
		return apiFetch( { path, parse } );
	},
	GET_FROM_API( { response } ) {
		return response.json();
	},
	CREATE_FROM_API( { path, data } ) {
		return apiFetch(
			{
				path: path,
				method: 'POST',
				body: data
			}
		);
	},
	UPDATE_FROM_API( { path, data } ) {
		return apiFetch(
			{
				path: path,
				method: 'PUT',
				data: data
			}
		);
	},
	DELETE_FROM_API( { path, relativePath, totalBytes } ) {
		return apiFetch(
			{
				path: path,
				method: 'DELETE',
				data: {
					relative_path: relativePath,
					total_bytes: totalBytes,
				}
			}
		);
	}
};
