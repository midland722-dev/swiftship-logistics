export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  // Allows to automatically instantiate createClient with right options
  // instead of createClient<Database, { PostgrestVersion: 'XX' }>(URL, KEY)
  __InternalSupabase: {
    PostgrestVersion: "14.5"
  }
  public: {
    Tables: {
      news_posts: {
        Row: {
          body: string | null
          created_at: string
          excerpt: string | null
          id: string
          published: boolean
          published_at: string | null
          slug: string
          title: string
          updated_at: string
        }
        Insert: {
          body?: string | null
          created_at?: string
          excerpt?: string | null
          id?: string
          published?: boolean
          published_at?: string | null
          slug: string
          title: string
          updated_at?: string
        }
        Update: {
          body?: string | null
          created_at?: string
          excerpt?: string | null
          id?: string
          published?: boolean
          published_at?: string | null
          slug?: string
          title?: string
          updated_at?: string
        }
        Relationships: []
      }
      pricing_rules: {
        Row: {
          base_fee: number
          currency: string
          express_multiplier: number
          fuel_surcharge_pct: number
          id: string
          insurance_fee: number
          insurance_pct: number
          is_active: boolean
          per_kg: number
          priority_multiplier: number
          standard_multiplier: number
          updated_at: string
          volumetric_divisor: number
        }
        Insert: {
          base_fee?: number
          currency?: string
          express_multiplier?: number
          fuel_surcharge_pct?: number
          id?: string
          insurance_fee?: number
          insurance_pct?: number
          is_active?: boolean
          per_kg?: number
          priority_multiplier?: number
          standard_multiplier?: number
          updated_at?: string
          volumetric_divisor?: number
        }
        Update: {
          base_fee?: number
          currency?: string
          express_multiplier?: number
          fuel_surcharge_pct?: number
          id?: string
          insurance_fee?: number
          insurance_pct?: number
          is_active?: boolean
          per_kg?: number
          priority_multiplier?: number
          standard_multiplier?: number
          updated_at?: string
          volumetric_divisor?: number
        }
        Relationships: []
      }
      profiles: {
        Row: {
          account_type: string | null
          company: string | null
          created_at: string
          full_name: string | null
          id: string
          onboarded_at: string | null
          phone: string | null
          updated_at: string
        }
        Insert: {
          account_type?: string | null
          company?: string | null
          created_at?: string
          full_name?: string | null
          id: string
          onboarded_at?: string | null
          phone?: string | null
          updated_at?: string
        }
        Update: {
          account_type?: string | null
          company?: string | null
          created_at?: string
          full_name?: string | null
          id?: string
          onboarded_at?: string | null
          phone?: string | null
          updated_at?: string
        }
        Relationships: []
      }
      push_subscriptions: {
        Row: {
          auth_key: string
          created_at: string
          endpoint: string
          id: string
          p256dh: string
          user_id: string
        }
        Insert: {
          auth_key: string
          created_at?: string
          endpoint: string
          id?: string
          p256dh: string
          user_id: string
        }
        Update: {
          auth_key?: string
          created_at?: string
          endpoint?: string
          id?: string
          p256dh?: string
          user_id?: string
        }
        Relationships: []
      }
      quotes: {
        Row: {
          created_at: string
          currency: string
          from_location: string
          height_cm: number | null
          id: string
          insurance: boolean
          length_cm: number | null
          owner_id: string
          price: number
          service_speed: string
          to_location: string
          weight_kg: number
          width_cm: number | null
        }
        Insert: {
          created_at?: string
          currency?: string
          from_location: string
          height_cm?: number | null
          id?: string
          insurance?: boolean
          length_cm?: number | null
          owner_id: string
          price: number
          service_speed: string
          to_location: string
          weight_kg: number
          width_cm?: number | null
        }
        Update: {
          created_at?: string
          currency?: string
          from_location?: string
          height_cm?: number | null
          id?: string
          insurance?: boolean
          length_cm?: number | null
          owner_id?: string
          price?: number
          service_speed?: string
          to_location?: string
          weight_kg?: number
          width_cm?: number | null
        }
        Relationships: []
      }
      service_bulletins: {
        Row: {
          active: boolean
          body: string | null
          created_at: string
          id: string
          severity: string
          title: string
        }
        Insert: {
          active?: boolean
          body?: string | null
          created_at?: string
          id?: string
          severity?: string
          title: string
        }
        Update: {
          active?: boolean
          body?: string | null
          created_at?: string
          id?: string
          severity?: string
          title?: string
        }
        Relationships: []
      }
      shipment_alert_prefs: {
        Row: {
          email_enabled: boolean
          phone_e164: string | null
          push_enabled: boolean
          sms_enabled: boolean
          updated_at: string
          user_id: string
        }
        Insert: {
          email_enabled?: boolean
          phone_e164?: string | null
          push_enabled?: boolean
          sms_enabled?: boolean
          updated_at?: string
          user_id: string
        }
        Update: {
          email_enabled?: boolean
          phone_e164?: string | null
          push_enabled?: boolean
          sms_enabled?: boolean
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      shipment_events: {
        Row: {
          created_at: string
          id: string
          label: string
          location: string | null
          occurred_at: string
          shipment_id: string
        }
        Insert: {
          created_at?: string
          id?: string
          label: string
          location?: string | null
          occurred_at?: string
          shipment_id: string
        }
        Update: {
          created_at?: string
          id?: string
          label?: string
          location?: string | null
          occurred_at?: string
          shipment_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "shipment_events_shipment_id_fkey"
            columns: ["shipment_id"]
            isOneToOne: false
            referencedRelation: "shipments"
            referencedColumns: ["id"]
          },
        ]
      }
      shipment_templates: {
        Row: {
          created_at: string
          from_location: string | null
          height_cm: number | null
          id: string
          insurance: boolean | null
          length_cm: number | null
          name: string
          owner_id: string
          service_speed: string | null
          to_location: string | null
          weight_kg: number | null
          width_cm: number | null
        }
        Insert: {
          created_at?: string
          from_location?: string | null
          height_cm?: number | null
          id?: string
          insurance?: boolean | null
          length_cm?: number | null
          name: string
          owner_id: string
          service_speed?: string | null
          to_location?: string | null
          weight_kg?: number | null
          width_cm?: number | null
        }
        Update: {
          created_at?: string
          from_location?: string | null
          height_cm?: number | null
          id?: string
          insurance?: boolean | null
          length_cm?: number | null
          name?: string
          owner_id?: string
          service_speed?: string | null
          to_location?: string | null
          weight_kg?: number | null
          width_cm?: number | null
        }
        Relationships: []
      }
      shipments: {
        Row: {
          created_at: string
          currency: string
          declared_value: number | null
          eta: string | null
          from_location: string
          height_cm: number | null
          id: string
          insurance: boolean
          length_cm: number | null
          notes: string | null
          owner_id: string | null
          price: number
          recipient_email: string | null
          recipient_name: string | null
          service_speed: string
          status: string
          to_location: string
          tracking_number: string
          updated_at: string
          weight_kg: number
          width_cm: number | null
        }
        Insert: {
          created_at?: string
          currency?: string
          declared_value?: number | null
          eta?: string | null
          from_location: string
          height_cm?: number | null
          id?: string
          insurance?: boolean
          length_cm?: number | null
          notes?: string | null
          owner_id?: string | null
          price: number
          recipient_email?: string | null
          recipient_name?: string | null
          service_speed: string
          status?: string
          to_location: string
          tracking_number?: string
          updated_at?: string
          weight_kg: number
          width_cm?: number | null
        }
        Update: {
          created_at?: string
          currency?: string
          declared_value?: number | null
          eta?: string | null
          from_location?: string
          height_cm?: number | null
          id?: string
          insurance?: boolean
          length_cm?: number | null
          notes?: string | null
          owner_id?: string | null
          price?: number
          recipient_email?: string | null
          recipient_name?: string | null
          service_speed?: string
          status?: string
          to_location?: string
          tracking_number?: string
          updated_at?: string
          weight_kg?: number
          width_cm?: number | null
        }
        Relationships: []
      }
      user_roles: {
        Row: {
          created_at: string
          id: string
          role: Database["public"]["Enums"]["app_role"]
          user_id: string
        }
        Insert: {
          created_at?: string
          id?: string
          role: Database["public"]["Enums"]["app_role"]
          user_id: string
        }
        Update: {
          created_at?: string
          id?: string
          role?: Database["public"]["Enums"]["app_role"]
          user_id?: string
        }
        Relationships: []
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      admin_grant_role: {
        Args: {
          _role: Database["public"]["Enums"]["app_role"]
          _target_email: string
        }
        Returns: undefined
      }
      admin_revoke_role: {
        Args: {
          _role: Database["public"]["Enums"]["app_role"]
          _target_user: string
        }
        Returns: undefined
      }
      generate_tracking_number: { Args: never; Returns: string }
      has_role: {
        Args: {
          _role: Database["public"]["Enums"]["app_role"]
          _user_id: string
        }
        Returns: boolean
      }
    }
    Enums: {
      app_role: "admin" | "staff" | "customer"
    }
    CompositeTypes: {
      [_ in never]: never
    }
  }
}

type DatabaseWithoutInternals = Omit<Database, "__InternalSupabase">

type DefaultSchema = DatabaseWithoutInternals[Extract<keyof Database, "public">]

export type Tables<
  DefaultSchemaTableNameOrOptions extends
    | keyof (DefaultSchema["Tables"] & DefaultSchema["Views"])
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof (DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
        DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Views"])
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? (DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
      DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Views"])[TableName] extends {
      Row: infer R
    }
    ? R
    : never
  : DefaultSchemaTableNameOrOptions extends keyof (DefaultSchema["Tables"] &
        DefaultSchema["Views"])
    ? (DefaultSchema["Tables"] &
        DefaultSchema["Views"])[DefaultSchemaTableNameOrOptions] extends {
        Row: infer R
      }
      ? R
      : never
    : never

export type TablesInsert<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Insert: infer I
    }
    ? I
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Insert: infer I
      }
      ? I
      : never
    : never

export type TablesUpdate<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof DatabaseWithoutInternals },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Update: infer U
    }
    ? U
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Update: infer U
      }
      ? U
      : never
    : never

export type Enums<
  DefaultSchemaEnumNameOrOptions extends
    | keyof DefaultSchema["Enums"]
    | { schema: keyof DatabaseWithoutInternals },
  EnumName extends DefaultSchemaEnumNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"]
    : never = never,
> = DefaultSchemaEnumNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"][EnumName]
  : DefaultSchemaEnumNameOrOptions extends keyof DefaultSchema["Enums"]
    ? DefaultSchema["Enums"][DefaultSchemaEnumNameOrOptions]
    : never

export type CompositeTypes<
  PublicCompositeTypeNameOrOptions extends
    | keyof DefaultSchema["CompositeTypes"]
    | { schema: keyof DatabaseWithoutInternals },
  CompositeTypeName extends PublicCompositeTypeNameOrOptions extends {
    schema: keyof DatabaseWithoutInternals
  }
    ? keyof DatabaseWithoutInternals[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"]
    : never = never,
> = PublicCompositeTypeNameOrOptions extends {
  schema: keyof DatabaseWithoutInternals
}
  ? DatabaseWithoutInternals[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"][CompositeTypeName]
  : PublicCompositeTypeNameOrOptions extends keyof DefaultSchema["CompositeTypes"]
    ? DefaultSchema["CompositeTypes"][PublicCompositeTypeNameOrOptions]
    : never

export const Constants = {
  public: {
    Enums: {
      app_role: ["admin", "staff", "customer"],
    },
  },
} as const
