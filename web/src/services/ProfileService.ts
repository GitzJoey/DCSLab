import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { client, useForm } from "laravel-precognition-vue";

export default class ProfileService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
  }

  public useUpdateUserProfileForm() {
    const url = route('api.post.db.module.profile.update.user_profile', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      name: '',
    });

    return form;
  }

  public useUpdatePersonalInfoForm() {
    const url = route('api.post.db.module.profile.update.personal_info', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      first_name: '',
      last_name: '',
      address: '',
      city: '',
      postal_code: '',
      country: '',
      img_path: '',
      tax_id: 0,
      ic_num: 0,
      status: '',
      remarks: '',
    });

    return form;
  }

  public useUpdateAccountSettingsForm() {
    const url = route('api.post.db.module.profile.update.account_settings', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      theme: 'side-menu-light-full',
      date_format: 'dd_MMM_yyyy',
      time_format: 'hh_mm_ss',
    });

    return form;
  }

  public useUpdateUserRolesForm() {
    const url = route('api.post.db.module.profile.update.roles', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      roles: '',
    });

    return form;
  }

  public useUpdatePasswordForm() {
    const url = route('api.post.db.module.profile.update.password', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      current_password: '',
      password: '',
      password_confirmation: '',
    });

    return form;
  }

  public useUpdateTokenForm() {
    const url = route('api.post.db.module.profile.update.tokens', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      reset_tokens: true,
    });

    return form;
  }
}
