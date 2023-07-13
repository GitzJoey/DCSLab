import { User } from "../models/User"

export interface UserFormRequest {
    data: User,
    additional: {
        tokens_reset: boolean
    }
}